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
			$productIdsFromBarcode = array_map(create_function('$a', 'return $a->getProduct()->getId();'), ProductCode::getAllByCriteria('code = ?', array($searchTxt)));
			$where = (count($productIdsFromBarcode) === 0 ? '' : ' OR id in (' . implode(',', $productIdsFromBarcode) . ')');
			foreach(Product::getAllByCriteria('name like :searchTxt OR sku like :searchTxt' . $where, array('searchTxt' => $searchTxt . '%'), true, 1, DaoQuery::DEFAUTL_PAGE_SIZE) as $product)
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
			var_dump($param->CallbackParameter);
			
			Dao::beginTransaction();
			$customer = Customer::get(trim($param->CallbackParameter->customer->id));
			if(!$customer instanceof Customer)
				throw new Exception('Invalid Customer passed in!');
			$paymentMethodId = PaymentMethod::get(trim($param->CallbackParameter->paymentMethodId));
			if(!$paymentMethodId instanceof PaymentMethod)
				throw new Exception('Invalid PaymentMethod passed in!');
			$courierId = Courier::get(trim($param->CallbackParameter->courierId));
			if(!$courierId instanceof Courier)
				throw new Exception('Invalid Courier passed in!');
			$comments = trim($param->CallbackParameter->comments);
			$totalPaidAmount = trim($param->CallbackParameter->totalPaidAmount);
			$totalShippingCost = trim($param->CallbackParameter->totalShippingCost);
			
// 			$supplierRefNum = trim($param->CallbackParameter->supplierRefNum);
// 			$supplierContactName = trim($param->CallbackParameter->contactName);
// 			$supplierContactNo = trim($param->CallbackParameter->contactNo);
// 			$shippingCost = trim($param->CallbackParameter->shippingCost);
// 			$handlingCost = trim($param->CallbackParameter->handlingCost);
// 			$comment = trim($param->CallbackParameter->comments);
// 			$status = trim($param->CallbackParameter->status);
// 			$purchaseOrder = PurchaseOrder::create($supplier,$supplierRefNum,$supplierContactName,$supplierContactNo,$shippingCost,$handlingCost);
// 			$purchaseOrderTotalAmount = trim($param->CallbackParameter->totalAmount);
// 			$purchaseOrderTotalPaid = trim($param->CallbackParameter->totalPaid);
// 			$purchaseOrder->setTotalAmount($purchaseOrderTotalAmount)
// 			->setTotalPaid($purchaseOrderTotalPaid)
// 			->setStatus($status);
// 			foreach ($param->CallbackParameter->items as $item) {
// 				$productId = trim($item->product->id);
// 				$productUnitPrice = trim($item->unitPrice);
// 				$qtyOrdered = trim($item->qtyOrdered);
// 				$productWtyOrdered = trim($item->qtyOrdered);
// 				$productTotalPrice = trim($item->totalPrice);
// 				$product = Product::get($productId);
// 				if(!$product instanceof Product)
// 					throw new Exception('Invalid Product passed in!');
// 				$purchaseOrder->addItem($product,$supplier->getId(),$productUnitPrice,$qtyOrdered,'','',$productTotalPrice);
// 			};
// 			$purchaseOrder->save();
// 			$purchaseOrder->addComment($comment, Comments::TYPE_SYSTEM);
// 			$results['item'] = $purchaseOrder->getJson();
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