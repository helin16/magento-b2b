<?php
/**
 * This is the OrderController
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'order.new';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		if(!isset($this->Request['id']))
			die('System ERR: no param passed in!');
		if(trim($this->Request['id']) === 'new')
			$order = new Order();
		else if(!($order = Order::get($this->Request['id'])) instanceof Order)
			die('Invalid Order!');
		
		if($order instanceof Order && trim($order->getType()) === Order::TYPE_INVOICE) {
			header('Location: /orderdetails/'. $order->getId() . '.html?' . $_SERVER['QUERY_STRING']);
			die();
		}
		
		$cloneOrder = null;
		if(isset($_REQUEST['cloneorderid']) && !($cloneOrder = Order::get(trim($_REQUEST['cloneorderid']))) instanceof Order)
			die('Invalid Order to clone from');

		$paymentMethods =  array_map(create_function('$a', 'return $a->getJson();'), PaymentMethod::getAll(true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('name' => 'asc')));
		$shippingMethods =  array_map(create_function('$a', 'return $a->getJson();'), Courier::getAll(true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('name' => 'asc')));
		$customer = (isset($_REQUEST['customerid']) && ($customer = Customer::get(trim($_REQUEST['customerid']))) instanceof Customer) ? $customer->getJson() : null;
		$js .= "pageJs";
			$js .= ".setHTMLIDs('detailswrapper')";
			$js .= ".setCallbackId('searchCustomer', '" . $this->searchCustomerBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('saveOrder', '" . $this->saveOrderBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('cancelOrder', '" . $this->cancelOrderBtn->getUniqueID() . "')";
			$js .= ".setPaymentMethods(" . json_encode($paymentMethods) . ")";
			$js .= ".setShippingMethods(" . json_encode($shippingMethods) . ")";
			$js .= ".setOrderTypes(" . json_encode(Order::getAllTypes()) . ")";
		if($order instanceof Order && trim($order->getId()) !== '') {
			$orderArray = $order->getJson();
			$orderArray['items'] = array_map(create_function('$a', 'return $a->getJson();'), OrderItem::getAllByCriteria('orderId = ?', array($order->getId())));
			$js .= ".setOrder(" . json_encode($orderArray) . ")";
		}
		if($cloneOrder instanceof Order) {
			$clonOrderArray = $cloneOrder->getJson();
			$clonOrderArray['items'] = array_map(create_function('$a', 'return $a->getJson();'), OrderItem::getAllByCriteria('orderId = ?', array($cloneOrder->getId())));
			$js .= ".setOriginalOrder(" . json_encode($clonOrderArray) . ")";
		}
		$js .= ".init(" . json_encode($customer) . ")";
		if(!AccessControl::canAccessCreateOrderPage(Core::getRole())) {
			$js .= ".disableEverything()";
			$js .= ".showModalBox('<h4>Error</h4>', '<h4>You DO NOT Have Access To This " . ($order instanceof Order ? $order->getType() : 'Page')  . "</h4>')";
		} else if($order instanceof Order  && intval($order->getStatus()->getId()) === OrderStatus::ID_CANCELLED ) {
			$js .= ".disableEverything()";
			$js .= ".showModalBox('<h4>Error</h4>', '<h4>This " . $order->getType()  . " has been " . $order->getStatus()->getName() . "!</h4><h4>No one can edit it anymore</h4>')";
		}
		$js .= ";";
		return $js;
	}
	/**
	 * Searching Customer
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function searchCustomer($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$pageNo = isset($param->CallbackParameter->pageNo) ? trim($param->CallbackParameter->pageNo) : 1;
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			$stats = array();
			foreach(Customer::getAllByCriteria('name like :searchTxt', array('searchTxt' => $searchTxt . '%'), true, $pageNo, DaoQuery::DEFAUTL_PAGE_SIZE, array('cust.name' => 'asc'), $stats) as $customer)
			{
				$items[] = $customer->getJson();
			}
			$results['items'] = $items;
			$results['pagination'] = $stats;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * Searching searchProduct
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function searchProduct($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			$where = 'pro_pro_code.code = :searchExact or pro.name like :searchTxt OR sku like :searchTxt';
			$params = array('searchExact' => $searchTxt, 'searchTxt' => '%' . $searchTxt . '%');
			$pageNo = isset($param->CallbackParameter->pageNo) ? trim($param->CallbackParameter->pageNo) : '1';
			$searchTxtArray = StringUtilsAbstract::getAllPossibleCombo(StringUtilsAbstract::tokenize($searchTxt));
			if(count($searchTxtArray) > 1)
			{
				foreach($searchTxtArray as $index => $comboArray)
				{
					$key = 'combo' . $index;
					$where .= ' OR pro.name like :' . $key;
					$params[$key] = '%' . implode('%', $comboArray) . '%';
				}
			}

			$supplierID = isset($param->CallbackParameter->supplierID) ? trim($param->CallbackParameter->supplierID) : '';
			Product::getQuery()->eagerLoad('Product.codes', 'left join');
			$stats = array();
			$products = Product::getAllByCriteria($where, $params, true, $pageNo, DaoQuery::DEFAUTL_PAGE_SIZE, array('pro.sku' => 'asc'), $stats);
			foreach($products as $product)
			{
				$items[] = $product->getJson();
			}
			$results['items'] = $items;
			$results['pagination'] = $stats;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * saveOrder
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function saveOrder($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			$customer = Customer::get(trim($param->CallbackParameter->customer->id));
			if(!$customer instanceof Customer)
				throw new Exception('Invalid Customer passed in!');
			if(!isset($param->CallbackParameter->type) || ($type = trim($param->CallbackParameter->type)) === '' || !in_array($type, Order::getAllTypes()))
				throw new Exception('Invalid type passed in!');
			$order = null;
			if(isset($param->CallbackParameter->orderId) && ($orderId = trim($param->CallbackParameter->orderId)) !== '') {
				if(!($order = Order::get($orderId)) instanceof Order)
					throw new Exception('Invalid Order to edit!');
			}
			$orderCloneFrom = null;
			if(isset($param->CallbackParameter->orderCloneFromId) && ($orderCloneFromId = trim($param->CallbackParameter->orderCloneFromId)) !== '') {
				if(!($orderCloneFrom = Order::get($orderCloneFromId)) instanceof Order)
					throw new Exception('Invalid Order to clone from!');
			}
			$shipped = ((isset($param->CallbackParameter->shipped) && (intval($param->CallbackParameter->shipped)) === 1));
			
			$poNo = (isset($param->CallbackParameter->poNo) && (trim($param->CallbackParameter->poNo) !== '') ? trim($param->CallbackParameter->poNo) : '');
			if(isset($param->CallbackParameter->shippingAddr)) {
				$shippAddress = ($order instanceof Order ? $order->getShippingAddr() : null);
				$shippAddress = Address::create(
					$param->CallbackParameter->shippingAddr->street,
					$param->CallbackParameter->shippingAddr->city,
					$param->CallbackParameter->shippingAddr->region,
					$param->CallbackParameter->shippingAddr->country,
					$param->CallbackParameter->shippingAddr->postCode,
					$param->CallbackParameter->shippingAddr->contactName,
					$param->CallbackParameter->shippingAddr->contactNo,
					$shippAddress
				);
			}
			else
				$shippAddress = $customer->getShippingAddress();
			$printItAfterSave = false;
			if(isset($param->CallbackParameter->printIt))
				$printItAfterSave = (intval($param->CallbackParameter->printIt) === 1 ? true : false);
			if(!$order instanceof Order)
				$order = Order::create($customer, $type, null, '', OrderStatus::get(OrderStatus::ID_NEW), new UDate(), false, $shippAddress, $customer->getBillingAddress(), false, $poNo, $orderCloneFrom);
			else {
				$order->setType($type)
					->setPONo($poNo)
					->save();
			}
			$totalPaymentDue = 0;
			if (trim($param->CallbackParameter->paymentMethodId))
			{
				$paymentMethod = PaymentMethod::get(trim($param->CallbackParameter->paymentMethodId));
				if(!$paymentMethod instanceof PaymentMethod)
					throw new Exception('Invalid PaymentMethod passed in!');
				$order->addInfo(OrderInfoType::ID_MAGE_ORDER_PAYMENT_METHOD, $paymentMethod->getName(), true);
				$totalPaidAmount = trim($param->CallbackParameter->totalPaidAmount);
						$order->addPayment($paymentMethod, $totalPaidAmount);
				if($shipped === true)
					$order->setType(Order::TYPE_INVOICE);
			}
			else
			{
				$paymentMethod = '';
				$totalPaidAmount = 0;
			}
			
			if(isset($param->CallbackParameter->courierId))
			{
				$totalShippingCost = 0;
				$courier = null;
				if(is_numeric($courierId = trim($param->CallbackParameter->courierId))) {
					$courier = Courier::get($courierId);
					if(!$courier instanceof Courier)
						throw new Exception('Invalid Courier passed in!');
					$order->addInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD, $courier->getName(), true);
				} else {
					$order->addInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD, $courierId, true);
				}
				if(isset($param->CallbackParameter->totalShippingCost)) {
					$totalShippingCost = StringUtilsAbstract::getValueFromCurrency(trim($param->CallbackParameter->totalShippingCost));
					$order->addInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_COST, StringUtilsAbstract::getCurrency($totalShippingCost), true);
				}
				if($shipped === true) {
					if(!$courier instanceof Courier)
						$courier = Courier::get(Courier::ID_LOCAL_PICKUP);
					Shippment::create($shippAddress, $courier, '', new UDate(), $order, '');
				}
			}
			else
			{
				$courier = '';
				$totalShippingCost = 0;
			}
			$totalPaymentDue += $totalShippingCost;
			$comments = trim($param->CallbackParameter->comments);
			$order = $order->addComment($comments, Comments::TYPE_SALES)
				->setTotalPaid($totalPaidAmount);

			foreach ($param->CallbackParameter->items as $item)
			{
				$product = Product::get(trim($item->product->id));
				if(!$product instanceof Product)
					throw new Exception('Invalid Product passed in!');
				$unitPrice = trim($item->unitPrice);
				$qtyOrdered = trim($item->qtyOrdered);
				$totalPrice = trim($item->totalPrice);
				$itemDescription = trim($item->itemDescription);

				if(intval($item->active) === 1)
					$totalPaymentDue += $totalPrice;
				if(!($orderItem = OrderItem::get($item->id)) instanceof OrderItem)
					$orderItem = OrderItem::create($order, $product, $unitPrice, $qtyOrdered, $totalPrice, 0, null, $itemDescription);
				else {
					$orderItem->setActive(intval($item->active))
						->setProduct($product)
						->setUnitPrice($unitPrice)
						->setQtyOrdered($qtyOrdered)
						->setTotalPrice($totalPrice)
						->setItemDescription($itemDescription)
						->save();
					SellingItem::deleteByCriteria('orderItemId = ?', array($orderItem->getId())); //DELETING ALL SERIAL NUMBER BEFORE ADDING
				}
				if(isset($item->serials)){
					foreach($item->serials as $serialNo)
						$orderItem->addSellingItem($serialNo);
				}
				if($shipped === true) {
					$orderItem->setIsPicked(true)
						->save();
				}
			}
			if($shipped === true) {
				$order->setStatus(OrderStatus::get(OrderStatus::ID_SHIPPED));
			}
			$order->setTotalAmount($totalPaymentDue)
				->save();

			$results['item'] = $order->getJson();
			if($printItAfterSave === true)
				$results['printURL'] = '/print/order/' . $order->getId() . '.html?pdf=1';
			$results['redirectURL'] = '/order/'. $order->getId() . '.html?' . $_SERVER['QUERY_STRING'];
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * cancelOrder
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function cancelOrder($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->orderId) || !($order = Order::get($param->CallbackParameter->orderId)) instanceof Order)
				throw new Exception('Invalid Order to CANCEL!');
			if(!isset($param->CallbackParameter->reason) || !($reason = trim($param->CallbackParameter->reason)) === '')
				throw new Exception('An reason for CANCELLING this ' . $order->getType() . ' is needed!');
			$order->setStatus(OrderStatus::get(OrderStatus::ID_CANCELLED))
				->save()
				->addComment(($msg = $order->getType() . ' is cancelled: ' . $reason), Comments::TYPE_SALES)
				->addLog($msg, Log::TYPE_SYSTEM, 'AUTO_GEN', __CLASS__ . '::' . __FUNCTION__);
			$results['item'] = $order->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>