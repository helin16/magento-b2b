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
		if(!$this->isPostBack)
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
		$order = FactoryAbastract::service('Order')->get($this->Request['orderId']);
		if(!$order instanceof Order)
			die('Invalid Order!');
		$js = parent::_getEndJs();
		
		$orderItems = array();
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
		
		$js .= 'pageJs.setEditMode(' . $purchaseEdit . ', ' . $warehouseEdit . ', ' . $accounEdit . ', ' . $statusEdit . ');';
		$js .= 'pageJs.setOrder('. json_encode($order->getJson()) . ', ' . json_encode($orderItems) . ', ' . json_encode($orderStatuses) . ');';
		$js .= 'pageJs.setCallbackId("updateOrder", "' . $this->updateOrderBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("getComments", "' . $this->getCommentsBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("addComments", "' . $this->addCommentsBtn->getUniqueID() . '");';
		$js .= 'pageJs.setCallbackId("changeOrderStatus", "' . $this->changeOrderStatusBtn->getUniqueID() . '");';
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
				$order->addComment('Changed from [ ' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_NORMAL);
			}
			if($for === 'warehouse')
			{
				if($allPicked === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_PICKED));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_INSUFFICIENT_STOCK));
				$order->addComment('Changed from [ ' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_NORMAL);
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
}
?>
