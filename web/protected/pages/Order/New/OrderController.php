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
		
		$paymentMethods =  array_map(create_function('$a', 'return $a->getJson();'), PaymentMethod::getAll());
		$shippingMethods =  array_map(create_function('$a', 'return $a->getJson();'), Courier::getAll());
		$customer = (isset($_REQUEST['customerid']) && ($customer = Customer::get(trim($_REQUEST['customerid']))) instanceof Customer) ? $customer->getJson() : null;
		$js .= "pageJs";
			$js .= ".setHTMLIDs('detailswrapper')";
			$js .= ".setCallbackId('searchCustomer', '" . $this->searchCustomerBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('saveOrder', '" . $this->saveOrderBtn->getUniqueID() . "')";
			$js .= ".setPaymentMethods(" . json_encode($paymentMethods) . ")";
			$js .= ".setShippingMethods(" . json_encode($shippingMethods) . ")";
			$js .= ".setOrderTypes(" . json_encode(array_filter(Order::getAllTypes(), create_function('$a', 'return $a !== "INVOICE";'))) . ")";
			$js .= ".init(" . json_encode($customer) . ");";
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
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			foreach(Customer::getAllByCriteria('name like :searchTxt or email like :searchTxt', array('searchTxt' => $searchTxt . '%')) as $customer)
			{
				$items[] = $customer->getJson();
			}
			$results['items'] = $items;
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
			$poNo = '';
			if(isset($param->CallbackParameter->poNo) && (trim($param->CallbackParameter->poNo) !== '') )
				$poNo = trim($param->CallbackParameter->poNo);
			$shipped = false;
			if(isset($param->CallbackParameter->shipped) && (intval($param->CallbackParameter->shipped)) === 1)
				$shipped = true;
			if(isset($param->CallbackParameter->shippingAddr))
				$shippAddress = Address::create(
					$param->CallbackParameter->shippingAddr->street, 
					$param->CallbackParameter->shippingAddr->city, 
					$param->CallbackParameter->shippingAddr->region, 
					$param->CallbackParameter->shippingAddr->country,
					$param->CallbackParameter->shippingAddr->postCode,
					$param->CallbackParameter->shippingAddr->contactName,
					$param->CallbackParameter->shippingAddr->contactNo
				);
			else
				$shippAddress = $customer->getShippingAddress();
			$printItAfterSave = false;
			if(isset($param->CallbackParameter->printIt))
				$printItAfterSave = (intval($param->CallbackParameter->printIt) === 1 ? true : false);
			
			$order = Order::create($customer, $type, null, '', OrderStatus::get(OrderStatus::ID_NEW), new UDate(), false, $shippAddress, $customer->getBillingAddress(), false, $poNo);
			$totalPaymentDue = 0;
			if (trim($param->CallbackParameter->paymentMethodId))
			{
				$paymentMethod = PaymentMethod::get(trim($param->CallbackParameter->paymentMethodId));
				if(!$paymentMethod instanceof PaymentMethod)
					throw new Exception('Invalid PaymentMethod passed in!');
				$order->addInfo(OrderInfoType::ID_MAGE_ORDER_PAYMENT_METHOD, $paymentMethod->getName());
				$totalPaidAmount = trim($param->CallbackParameter->totalPaidAmount);
				if($shipped === true) {
					$order->setPassPaymentCheck(true)
						->addPayment($paymentMethod, $totalPaidAmount);
				}
			} 
			else 
			{
				$paymentMethod = '';
				$totalPaidAmount = 0;
			}
			if(trim($param->CallbackParameter->courierId))
			{
				$courier = Courier::get(trim($param->CallbackParameter->courierId));
				if(!$courier instanceof Courier)
					throw new Exception('Invalid Courier passed in!');
				$order->addInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD, $courier->getName());
				$totalShippingCost = trim($param->CallbackParameter->totalShippingCost);
				if($shipped === true) {
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
			$order = $order->addComment($comments)
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
				
				$totalPaymentDue += $totalPrice;
				$orderItem = OrderItem::create($order, $product, $unitPrice, $qtyOrdered, $totalPrice, 0, null, $itemDescription);
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