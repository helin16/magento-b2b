<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class DetailsController extends BPCPageAbstract
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
		if(!isset($this->Request['id']))
			die('System ERR: no param passed in!');
		if(!($rma = RMA::get($this->Request['id'])) instanceof RMA && trim($this->Request['id']) !== 'new')
			die('Invalid $rma passed in!');
		
		$js = parent::_getEndJs();
		
		$customer = (isset($_REQUEST['customerid']) && ($customer = Customer::get(trim($_REQUEST['customerid']))) instanceof Customer) ? $customer->getJson() : null;
		if(isset($_REQUEST['orderid']) && !($order = Order::get(trim($_REQUEST['orderid']))) instanceof Order)
			die('Invalid Order passed in!');
		if(isset($_REQUEST['orderid']) && ($order = Order::get(trim($_REQUEST['orderid']))) instanceof Order && $rma instanceof RMA)
			die('You can ONLY create NEW Credit Note from an existing ORDER');
		$statusOptions = RMA::getAllStatuses();
		
		if(isset($_REQUEST['orderid']) && ($order = Order::get(trim($_REQUEST['orderid']))) instanceof Order)
			$js .= "pageJs._order=" . json_encode($order->getJson(array('customer'=> $order->getCustomer()->getJson(), 'items'=> array_map(create_function('$a', 'return $a->getJson(array("product"=>$a->getProduct()->getJson()));'), $order->getOrderItems())))) . ";";
		else $js .= "pageJs._customer=" . json_encode($customer) . ";";
		if($rma instanceof RMA)
			$js .= "pageJs._RMA=" . json_encode($rma->getJson(array('customer'=> $rma->getCustomer()->getJson(), 'items'=> array_map(create_function('$a', 'return $a->getJson(array("product"=>$a->getProduct()->getJson()));'), $rma->getRMAItems())))) . ";";
		$js .= "pageJs._statusOptions=" . json_encode($statusOptions) . ";";
		$js .= "pageJs";
			$js .= ".setHTMLIDs('detailswrapper')";
			$js .= ".setCallbackId('searchCustomer', '" . $this->searchCustomerBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('saveOrder', '" . $this->saveOrderBtn->getUniqueID() . "')";
			$js .= '.setCallbackId("addComments", "' . $this->addCommentsBtn->getUniqueID() . '")';
			$js .= ".init();";
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
			foreach(Customer::getAllByCriteria('name like :searchTxt or contactNo = :searchTxtExact or 	email = :searchTxtExact', array('searchTxt' => $searchTxt . '%', 'searchTxtExact' => $searchTxt)) as $customer)
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
			$pageNo = isset($param->CallbackParameter->pageNo) ? trim($param->CallbackParameter->pageNo) : '1';
			$where = 'pro_pro_code.code = :searchExact or pro.name like :searchTxt OR sku like :searchTxt';
			$params = array('searchExact' => $searchTxt , 'searchTxt' => '%' . $searchTxt . '%');
			
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
			$stats = array();
			$supplierID = isset($param->CallbackParameter->supplierID) ? trim($param->CallbackParameter->supplierID) : '';
			Product::getQuery()->eagerLoad('Product.codes', 'left join');
			$products = Product::getAllByCriteria($where, $params, true, $pageNo, DaoQuery::DEFAUTL_PAGE_SIZE, array('pro.sku' => 'asc'), $stats);
			foreach($products as $product)
			{
				$array = $product->getJson();
				
				$array['minProductPrice'] = 0;
				$array['lastSupplierPrice'] = 0;
				$array['minSupplierPrice'] = 0;
				
				$minProductPriceProduct = PurchaseOrderItem::getAllByCriteria('productId = ?', array($product->getId()), true, 1, 1, array('unitPrice'=> 'asc'));
				$minProductPrice = sizeof($minProductPriceProduct) ? $minProductPriceProduct[0]->getUnitPrice() : 0;
				$minProductPriceId = sizeof($minProductPriceProduct) ? $minProductPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				PurchaseOrderItem::getQuery()->eagerLoad('PurchaseOrderItem.purchaseOrder');
				$lastSupplierPriceProduct = PurchaseOrderItem::getAllByCriteria('po_item.productId = ? and po_item_po.supplierId = ?', array($product->getId(), $supplierID), true, 1, 1, array('po_item.id'=> 'desc'));
				$lastSupplierPrice = sizeof($lastSupplierPriceProduct) ? $lastSupplierPriceProduct[0]->getUnitPrice() : 0;
				$lastSupplierPriceId = sizeof($lastSupplierPriceProduct) ? $lastSupplierPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				PurchaseOrderItem::getQuery()->eagerLoad('PurchaseOrderItem.purchaseOrder');
				$minSupplierPriceProduct = PurchaseOrderItem::getAllByCriteria('po_item.productId = ? and po_item_po.supplierId = ?', array($product->getId(), $supplierID), true, 1, 1, array('po_item.unitPrice'=> 'asc'));
				$minSupplierPrice = sizeof($minSupplierPriceProduct) ? $minSupplierPriceProduct[0]->getUnitPrice() : 0;
				$minSupplierPriceId = sizeof($minSupplierPriceProduct) ? $minSupplierPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				$array['minProductPrice'] = $minProductPrice;
				$array['minProductPriceId'] = $minProductPriceId;
				
				$array['lastSupplierPrice'] = $lastSupplierPrice;
				$array['lastSupplierPriceId'] = $lastSupplierPriceId;
				
				$array['minSupplierPrice'] = $minSupplierPrice;
				$array['minSupplierPriceId'] = $minSupplierPriceId;
				
				$items[] = $array;
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
			if(!isset($param->CallbackParameter->status) || ($status = trim($param->CallbackParameter->status)) === '' || !in_array($status, RMA::getAllStatuses()))
				throw new Exception('Invalid Status passed in!');
			if(isset($param->CallbackParameter->RMA) && !($RMA = RMA::get(trim($param->CallbackParameter->RMA->id))) instanceof RMA)
				throw new Exception('Invalid RMA To passed in!');
			$RMA = (isset($param->CallbackParameter->RMA) && ($RMA = RMA::get(trim($param->CallbackParameter->RMA->id))->setDescription(trim($param->CallbackParameter->description))) instanceof RMA) ? $RMA : RMA::create($customer, trim($param->CallbackParameter->description));
			if(isset($param->CallbackParameter->order) && ($order = Order::get(trim($param->CallbackParameter->order->id))) instanceof Order)
				$RMA->setOrder($order);
			
			if(isset($param->CallbackParameter->shippingAddr))
			{
				$shippAddress = Address::create(
					$param->CallbackParameter->shippingAddr->street,
					$param->CallbackParameter->shippingAddr->city,
					$param->CallbackParameter->shippingAddr->region,
					$param->CallbackParameter->shippingAddr->country,
					$param->CallbackParameter->shippingAddr->postCode,
					$param->CallbackParameter->shippingAddr->contactName,
					$param->CallbackParameter->shippingAddr->contactNo
				);
				$customer->setShippingAddress($shippAddress);
			}
			
			$printItAfterSave = false;
			if(isset($param->CallbackParameter->printIt))
				$printItAfterSave = (intval($param->CallbackParameter->printIt) === 1 ? true : false);
			
			if(isset($param->CallbackParameter->comments))
			{
				$comments = trim($param->CallbackParameter->comments);
				$RMA->addComment($comments, Comments::TYPE_SALES);
			}
			
			$totalPaymentDue = 0;
			foreach ($param->CallbackParameter->items as $item)
			{
				$product = Product::get(trim($item->product->id));
				if(!$product instanceof Product)
					throw new Exception('Invalid Product passed in!');
				$unitPrice = trim($item->unitPrice);
				$qtyOrdered = trim($item->qtyOrdered);
				$totalPrice = trim($item->totalPrice);
				$itemDescription = trim($item->itemDescription);
				$active = trim($item->valid);
				
				$totalPaymentDue += $totalPrice;
				if(is_numeric($item->RMAItemId) && !RMAItem::get(trim($item->RMAItemId)) instanceof RMAItem)
					throw new Exception('Invalid RMA Item passed in');
				$RMAItem = is_numeric($item->RMAItemId) ? 
					RMAItem::get(trim($item->RMAItemId))->setActive($active)->setProduct($product)->setQty($qtyOrdered)->setItemDescription($itemDescription)
					: 
					RMAItem::create($RMA, $product, $qtyOrdered, $itemDescription);
				$RMAItem->save();
				if(isset($item->RMAItemId) && ($RMAItem = RMAItem::get(trim($item->RMAItemId))) instanceof RMAItem && !empty($product->getUnitCost()))
					$RMAItem->setUnitCost($RMAItem->getUnitCost())->save();
				else $RMAItem->setUnitCost($product->getUnitCost())->save();
			}
			$RMA->setTotalValue($totalPaymentDue)->setStatus($status)->save();
			$results['item'] = $RMA->getJson();
// 			if($printItAfterSave === true)
// 				$results['printURL'] = '/print/rma/' . $RMA->getId() . '.html?pdf=1';
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
// 			$errors[] = $ex->getMessage();
			$errors[] = $ex->getTraceAsString();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
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
			if(!isset($params->CallbackParameter->RMA) || !($RMA = RMA::get($params->CallbackParameter->RMA->id)) instanceof RMA)
				throw new Exception('System Error: invalid CreditNote passed in!');
			if(!isset($params->CallbackParameter->comments) || ($comments = trim($params->CallbackParameter->comments)) === '')
				throw new Exception('System Error: invalid comments passed in!');
			$comment = Comments::addComments($RMA, $comments, Comments::TYPE_NORMAL);
			$results = $comment->getJson();
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
