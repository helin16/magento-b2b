<?php
/**
 * This is the OrderDetailsController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderDetailsController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'order';
	/**
	 * The order that we are viewing
	 * 
	 * @var Order
	 */
	public $order = null;
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!$this->isPostBack && !$this->isCallBack)
		{
		}
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		if(!($order = FactoryAbastract::service('Order')->get($this->Request['orderId'])) instanceof Order)
			die('Invalid Order!');
		
		$js = parent::_getEndJs();
		
		$orderItems = $courierArray = array();
		foreach($order->getOrderItems() as $orderItem)
			$orderItems[] = $orderItem->getJson();
		$purchaseEdit = $warehouseEdit = $accounEdit = $statusEdit = 'false';
		if($order->canEditBy(Core::getRole()))
		{
			$purchaseEdit = ($order->canEditBy(FactoryAbastract::service('Role')->get(Role::ID_PURCHASING))) ? 'true' : 'false';
			$warehouseEdit = ($order->canEditBy(FactoryAbastract::service('Role')->get(Role::ID_WAREHOUSE))) ? 'true' : 'false';
			$accounEdit = ($order->canEditBy(FactoryAbastract::service('Role')->get(Role::ID_ACCOUNTING))) ? 'true' : 'false';
			$statusEdit = ($order->canEditBy(FactoryAbastract::service('Role')->get(Role::ID_STORE_MANAGER)) || $order->canEditBy(FactoryAbastract::service('Role')->get(Role::ID_SYSTEM_ADMIN))) ? 'true' : 'false';
		}
		
		$orderStatuses = array();
		foreach(OrderStatus::findAll() as $status)
			$orderStatuses[] = $status->getJson();
		
		foreach(Courier::findAll() as $courier)
			$courierArray[] = $courier->getJson();
		
		$js .= 'pageJs.setEditMode(' . $purchaseEdit . ', ' . $warehouseEdit . ', ' . $accounEdit . ', ' . $statusEdit . ');';
		$js .= 'pageJs.setOrder('. json_encode($order->getJson()) . ', ' . json_encode($orderItems) . ', ' . json_encode($orderStatuses) . ');';
		$js .= 'pageJs.setCourier('. json_encode($courierArray) . ');';
		$js .= 'pageJs.setCallbackId("updateOrder", "' . $this->updateOrderBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("getComments", "' . $this->getCommentsBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("addComments", "' . $this->addCommentsBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("confirmPayment", "' . $this->confirmPaymentBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("changeOrderStatus", "' . $this->changeOrderStatusBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("updateOIForWH", "' . $this->updateOIForWHBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("updateShippingInfo", "' . $this->updateShippingInfoBtn->getUniqueID() . '");';
		$js .= 'pageJs.load("detailswrapper");';
		return $js;
	}
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function updateOrder($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			
			if(!isset($params->CallbackParameter->for) || ($for = trim($params->CallbackParameter->for)) === '')
				throw new Exception('System Error: invalid for passed in!');
			
			if(!$order->canEditBy(Core::getRole()))
				throw new Exception('You do NOT edit this order as ' . Core::getRole() . '!');
			
			$hasETA = false;
			$allPicked = true;
			
			$commentType = ($for === 'purchasing' ? Comments::TYPE_PURCHASING : Comments::TYPE_WAREHOUSE);
			foreach($params->CallbackParameter->items as $obj)
			{
				if(!($orderItem = FactoryAbastract::service('OrderItem')->get($obj->orderItem->id)) instanceof OrderItem)
					$orderItem = new OrderItem();
				
				$orderItem->setQtyOrdered($obj->orderItem->qtyOrdered);
				$orderItem->setUnitPrice($obj->orderItem->unitPrice);
				$orderItem->setTotalPrice($obj->orderItem->totalPrice);
				$orderItem->setOrder($order);
				$sku = trim($obj->orderItem->product->sku);
				$orderItem->setProduct(Product::get($sku));
				FactoryAbastract::service('OrderItem')->save($orderItem);
				
				if(!isset($obj->$for))
					throw new Exception('System Error: ' . $for .' is NOT defined!');
				$comments = isset($obj->$for->comments) ? trim($obj->$for->comments) : '';
				if($comments !== '')
					$orderItem->addComment($comments, $commentType);
				if(isset($obj->$for->eta))
				{
					$eta = trim($obj->$for->eta);
					$orderItem->setEta($eta === '' ? null : $eta);
					if($eta!== '' && $eta !== trim(UDate::zeroDate()))
					{
						$order->addComment('Added ETA[' . $eta . '] for product(SKU=' . $sku .'): ' . $comments, $commentType);
						$hasETA = true;
					}
				}
				
				if(isset($obj->$for->isPicked))
				{
					$picked = (trim($obj->$for->isPicked) === 'Y');
					$orderItem->setIsPicked($picked);
					if($picked === false)
					{
						$order->addComment('Picked product(SKU=' . $sku .'): ' . $comments, $commentType);
						$allPicked = false;
					}
				}
				FactoryAbastract::service('OrderItem')->save($orderItem);
			}
			
			$status = trim($order->getStatus());
			if($for === 'purchasing')
			{
				if($hasETA === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_ETA));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING));
				$order->addComment('Changed from [' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_SYSTEM);
			}
			if($for === 'warehouse')
			{
				if($allPicked === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_PICKED));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_INSUFFICIENT_STOCK));
				$order->addComment('Changed from [' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_SYSTEM);
			}
			
			FactoryAbastract::service('Order')->save($order);
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * 
	 * @param Comments $comments
	 * @return multitype:string
	 */
	private function _formatComments(Comments $comments)
	{
		$array = array();
		$created = new UDate($comments->getCreated());
		$created->setTimeZone(SystemSettings::getSettings(SystemSettings::TYPE_SYSTEM_TIMEZONE));
		$array['created'] = trim($created);
		$array['creator'] = trim($comments->getCreatedBy()->getPerson());
		$array['comments'] = trim($comments->getComments());
		$array['type'] = trim($comments->getType());
		return $array;
	}
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function getComments($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			$type = isset($params->CallbackParameter->type) ? trim($params->CallbackParameter->type) : '';
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($params->CallbackParameter->pagination))
			{
				$pageNo = isset($params->CallbackParameter->pagination->pageNo) ? trim($params->CallbackParameter->pagination->pageNo) : $pageNo;
				$pageSize = isset($params->CallbackParameter->pagination->pageSize) ? trim($params->CallbackParameter->pagination->pageSize) : $pageSize;
			}
			$items = array();
			$pageStats = array();
			$commentsArray = $order->getComment($type, $pageNo, $pageSize, array('`comm`.id' => 'desc'), $pageStats);
			foreach($commentsArray as $comments)
				$items[] = $this->_formatComments($comments);
			
			$results['items'] = $items;
			$results['pagination'] = $pageStats;
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function addComments($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->comments) || ($comments = trim($params->CallbackParameter->comments)) === '')
				throw new Exception('System Error: invalid comments passed in!');
			$comment = Comments::addComments($order, $comments, Comments::TYPE_NORMAL);
			$results = $this->_formatComments($comment);
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function changeOrderStatus($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->orderStatusId) || !($orderStatus = OrderStatus::get($params->CallbackParameter->orderStatusId)) instanceof OrderStatus)
				throw new Exception('System Error: invalid orderStatus passed in!');
			if(!isset($params->CallbackParameter->comments) || ($comments = trim($params->CallbackParameter->comments)) === '')
				throw new Exception('System Error: comments not provided!');
			
			$oldStatus = $order->getStatus();
			$order->setStatus($orderStatus);
			$order->addComment('change Status from [' . $oldStatus. '] to [' . $order->getStatus() . ']: ' . $comments, Comments::TYPE_NORMAL);
			FactoryAbastract::service('Order')->save($order);
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * @throws Exception
	 */
	public function confirmPayment($sender, $params)
	{
		$results = $errors = array();
		$commentString = "";
		
		try 
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->paidAmt) || ($paidAmount = trim($params->CallbackParameter->paidAmt)) === '' || !is_numeric($paidAmount))
				throw new Exception('System Error: invalid Paid Amount passed in!');
			if(!isset($params->CallbackParameter->amtDiff) || ($amountDiff = trim($params->CallbackParameter->amtDiff)) === '' || !is_numeric($amountDiff))
				throw new Exception('System Error: Invalid Amount Difference passed in!');
			if(!isset($params->CallbackParameter->extraComment))
				throw new Exception('System Error: Invalid Extra Comment passed in!');
			if(($extraComment = trim($params->CallbackParameter->extraComment)) === '' && $amountDiff !== '0')
				throw new Exception('Additional Comment is Mandatory as the Paid Amount is not mathcing with the Total Amount!');
			
			$order->setTotalPaid($paidAmount);
			$order->setPassPaymentCheck(true);
			FactoryAbastract::service('Order')->save($order);
			
			$commentString = "Total Amount Due was ".$order->getTotalAmount().". And total amount paid is ".$paidAmount.".";
			
			if(($amtDiff = $order->getTotalAmount() - $paidAmount) === 0)
				$commentString = "Amount is fully paid.".$commentString;
			
			$commentString = '['.$commentString.']'.($extraComment !== '' ? ' : '.$extraComment : '');
			$comment = Comments::addComments($order, $commentString, Comments::TYPE_ACCOUNTING);
			$results = $this->_formatComments($comment);
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $params
	 * @throws Exception
	 */
	public function updateOrderItemForWarehouse($sender, $params)
	{
		$results = $errors = array();
		$counter = 0;
		$allItemsPicked = true;
		
		try 
		{
			Dao::beginTransaction();
			
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->orderItems) || !is_array($orderItemArray = $params->CallbackParameter->orderItems) || count($orderItemArray) === 0)
				throw new Exception('System Error: invalid order items passed in!');
			
			foreach($orderItemArray as $oi)
			{
				if(!($orderItem = FactoryAbastract::service('OrderItem')->get($oi->orderItem->id)) instanceof OrderItem)
					throw new Exception('System Error: invalid order item with id ['.$oi->orderItem->id.'] passed in!');
				
				if(!isset($oi->warehouse->isPicked) || (($isPicked = trim($oi->warehouse->isPicked)) === 'N' && (!isset($oi->warehouse->comments) || ($pickedComment = trim($oi->warehouse->comments)) === '')))
					throw new Exception('System Error: isPicked information not passed in OR isPicked is false but no comments have been provided');

				$orderItem->setIsPicked(($isPicked === 'Y' ? true : false));
				FactoryAbastract::service('OrderItem')->save($orderItem);
				$results[$counter]['orderItem'] = $orderItem;
				$results[$counter]['comment'] = array();

				if($isPicked === 'N')
				{
					$comment = Comments::addComments($orderItem, $pickedComment, Comments::TYPE_WAREHOUSE);
					$this->_formatComments($comment);
					$results[$counter]['comment'] = $comment;
					$allItemsPicked = false;
				}
				$counter++;
			}
			
			$newStatus = null;
			if($allItemsPicked === true)
				$newStatus = FactoryAbastract::service('OrderStatus')->get(OrderStatus::ID_PICKED);
			else
				$newStatus = FactoryAbastract::service('OrderStatus')->get(OrderStatus::ID_INSUFFICIENT_STOCK);
			
			$order->setStatus($newStatus);
			FactoryAbastract::service('Order')->save($order);
			
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	
	/**
	 * This function validates the Shipping info received from the JS 
	 * 
	 * @param Array $shippingInfoArray
	 * @param Array $validColumns
	 * 
	 * @throws Exception
	 * @return OrderDetailsController
	 */
	private function _validateShppingDetails($shippingInfoArray, $validColumns)
	{
		foreach($validColumns as $vc)
		{
			if(!isset($shippingInfoArray->$vc))
				throw new Exception('System Error: Incomplete Shipping Info Details provided!!!');
			
			if((!is_array($shippingInfoArray->$vc) || count($shippingInfoArray->$vc) === 0))
				throw new Exception('System Error: Mandatory Information ['.$vc.'] missing');
		}
		return $this;
	}
	
	/**
	 * 
	 * @param unknown $sender
	 * @param unknown $param
	 */
	public function updateShippingDetails($sender, $params)
	{
		$result = $error = $shippingInfoArray = array();
		$validColumns = array('courierId', 'contactNo', 'contactName', 'street', 'city', 'region', 'country', 'postCode', 'noOfCartons', 'conNoteNo', 'estShippingCost');
		
		try 
		{
			Dao::beginTransaction();
			
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->shippingInfo))
				throw new Exception('System Error: invalid Shipping Info Details passed in!');
			
			if(!$order->getStatus() instanceof OrderStatus || trim($order->getStatus()->getId()) !== trim(OrderStatus::ID_PICKED))
				throw new Exception('System Error: Order ['.$order->getOrderNo().'] Is Not is PICKED status. Current status is ['.($order->getStatus() instanceof OrderStatus ? $order->getStatus()->getName() : 'NULL').']');
			
			$shippingInfoArray = $params->CallbackParameter->shippingInfo;
			$this->_validateShppingDetails($shippingInfoArray, $validColumns);
			
			if(!($courier = FactoryAbastract::service('Courier')->get($shippingInfoArray->courierId[0])) instanceof Courier)
				throw new Exception('Invalid Courier Id ['.$shippingInfoArray->courierId[0].'] provided');
			
			$contactName = implode(",", $shippingInfoArray->contactName);
			$contactNo = implode(",", $shippingInfoArray->contactNo);
			$street = trim($shippingInfoArray->street[0]);
			$city = trim($shippingInfoArray->city[0]);
			$region = trim($shippingInfoArray->region[0]);
			$country = trim($shippingInfoArray->country[0]);
			$postCode = trim($shippingInfoArray->postCode[0]);
			$noOfCartons = trim($shippingInfoArray->noOfCartons[0]);
			$consignmentNo = trim($shippingInfoArray->conNoteNo[0]);
			$estShippingCost = trim($shippingInfoArray->estShippingCost[0]);
			$deliveryInstructions = (isset($shippingInfoArray->deliveryInstructions) ? implode(", ", $shippingInfoArray->deliveryInstructions) : '');
			
			$shippingAddress = $street.', '.$city.', '.$region.', '.$country.' '.$postCode;
			
			$shipment = new Shippment();
			$shipment->setOrder($order);
			$shipment->setCourier($courier);
			$shipment->setNoOfCartons($noOfCartons);
			$shipment->setReceiver($contactName);
			$shipment->setAddress($shippingAddress);
			$shipment->setContact($contactNo);
			$shipment->setShippingDate(new UDate("now"));
			$shipment->setConNoteNo($consignmentNo);
			$shipment->setEstShippingCost($estShippingCost);
			$shipment->setDeliveryInstructions($deliveryInstructions);
			$shipment->setActive(true);
			$shipment = FactoryAbastract::dao('Shippment')->save($shipment);
			
			$order->setStatus(FactoryAbastract::service('OrderStatus')->get(OrderStatus::ID_SHIPPED));
			FactoryAbastract::service('Order')->save($order);
			
			$result['shipment'] = $shipment->getJson();
			
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$error[] = $ex->getMessage();
		}
		
		$params->ResponseData = StringUtilsAbstract::getJson($result, $error);
		
	}
}
?>
