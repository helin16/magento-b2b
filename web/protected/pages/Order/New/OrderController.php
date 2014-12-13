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
			$products = Product::getAllByCriteria($where, $params, true, 1, DaoQuery::DEFAUTL_PAGE_SIZE, array('pro.sku' => 'asc'));
			foreach($products as $product)
			{
				$items[] = $product->getJson();
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
// 			var_dump($param->CallbackParameter);
			
			Dao::beginTransaction();
			$customer = Customer::get(trim($param->CallbackParameter->customer->id));
			if(!$customer instanceof Customer)
				throw new Exception('Invalid Customer passed in!');
			
			$totalPaymentDue = 0;
			$order = Order::create($customer);
			if (trim($param->CallbackParameter->paymentMethodId))
			{
				$paymentMethod = PaymentMethod::get(trim($param->CallbackParameter->paymentMethodId));
				if(!$paymentMethod instanceof PaymentMethod)
					throw new Exception('Invalid PaymentMethod passed in!');
				$order->addInfo(OrderInfoType::ID_MAGE_ORDER_PAYMENT_METHOD, $paymentMethod->getName());
				$totalPaidAmount = trim($param->CallbackParameter->totalPaidAmount);
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
				
				$totalPaymentDue += $totalPrice;
				$orderItem = OrderItem::create($order, $product, $unitPrice, $qtyOrdered, $totalPrice);
				if(isset($item->serials)){
					foreach($item->serials as $serialNo)
						$orderItem->addSellingItem($serialNo);
				}
			}
			$order->setTotalAmount($totalPaymentDue)
				->save();
			
			$results['item'] = $order->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>