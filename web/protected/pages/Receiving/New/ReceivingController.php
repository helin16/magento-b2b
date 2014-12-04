<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ReceivingController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'purchaseorders.receiving';
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
		$js .= "pageJs";
			$js .= ".setHTMLIDs('detailswrapper','search_panel','payment_panel','supplier_info_panel','order_change_details_table','barcode_input')";
			$js .= ".setCallbackId('searchPO', '" . $this->searchPOBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('saveOrder', '" . $this->saveOrderBtn->getUniqueID() . "')";
			$js .= ".setCallbackId('checkProduct', '" . $this->checkProductBtn->getUniqueID() . "')";
			$js .= ".init();";
		return $js;
	}
	/**
	 * Searching PO
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function searchPO($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
			if($searchTxt === '')
				$results['items'] = '';
			else {
				foreach(PurchaseOrder::getAllByCriteria('(purchaseOrderNo like :searchTxt || supplierRefNo like :searchTxt) && (status = :statusReceiving || status = :statusOrdered)', array('searchTxt' => $searchTxt . '%', 'statusReceiving' => PurchaseOrder::STATUS_RECEIVING, 'statusOrdered' => PurchaseOrder::STATUS_ORDERED), true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('id'=> 'desc')) as $po)
				{
					$array = $po->getJson();
					$array['totalProdcutCount'] = $po->gettotalProdcutCount();
					$items[] = $array;
				}
				$results['items'] = $items;
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * check product: if the PO contain such product
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 *
	 */
	public function checkProduct($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			var_dump($param->CallbackParameter);
			$purchaseOrder = PurchaseOrder::get(trim($param->CallbackParameter->purchaseOrder->id));
			$product = Product::get(trim($param->CallbackParameter->product->id));
			$results['count'] = PurchaseOrderItem::countByCriteria('purchaseOrderId = :purchaseOrderId and productId = :productId', array('purchaseOrderId' => $purchaseOrder->getId(), 'productId' => $product->getId()));
			
// 			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
// 			if($searchTxt === '')
// 				$results['items'] = '';
// 			else {
// 				foreach(PurchaseOrder::getAllByCriteria('(purchaseOrderNo like :searchTxt || supplierRefNo like :searchTxt) && (status = :statusReceiving || status = :statusOrdered)', array('searchTxt' => $searchTxt . '%', 'statusReceiving' => PurchaseOrder::STATUS_RECEIVING, 'statusOrdered' => PurchaseOrder::STATUS_ORDERED), true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('id'=> 'desc')) as $po)
// 				{
// 					$array = $po->getJson();
// 					$array['totalProdcutCount'] = $po->gettotalProdcutCount();
// 					$items[] = $array;
// 				}
// 				$results['items'] = $items;
// 			}
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
			var_dump($param->CallbackParameter);
			$searchTxt = isset($param->CallbackParameter->searchTxt) ? trim($param->CallbackParameter->searchTxt) : '';
// 			$purchaseOrder = PurchaseOrderItem::get(trim($param->CallbackParameter->purchaseOrder->id));
			Product::getQuery()->eagerLoad('Product.codes', 'left join');
			$products = Product::getAllByCriteria('pro_pro_code.code = :searchExact or pro.sku = :searchTxt or pro.name = :searchTxt', array('searchExact' => $searchTxt, 'searchTxt' => '%' . $searchTxt . '%'), true, 1, DaoQuery::DEFAUTL_PAGE_SIZE * 3);
// 			PurchaseOrderItem::countByCriteria('purchaseOrderId = :purchaseOrderId and productId = :productId');
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
			var_dump($param->CallbackParameter);
			$results['item'] = 'works';
			
			Dao::beginTransaction();
			foreach ($serialnos as $serialNo)
			{
				$item = ReceivingItem::create($po, $product);
			}
			$msg = 'received ' . count($serialnos) . ' product(SKU=' . $produt->getSku() . ') by ' . Core::getUser()->getPerson()->getFullName() . '@' . trim(new UDate()) . '(UTC)';
			$nofullReceivedItems = PurchaseOrderItem::getAllByCriteria('productId = ? and purchaseOrderId = ? and receivedQty < qty', array($product->getId(), $po->getId()), true, 1, 1);
			if(count($nofullReceivedItems) > 0)
			{
				$nofullReceivedItems[0]
					->setReceivedQty($nofullReceivedItems[0]->GetReceivedQty() + count($serialnos))
					->save()
					->addLog(Log::TYPE_SYSTEM, $msg, __CLASS__ . '::' . __FUNCTION__)
					->addComments(Comments::TYPE_WAREHOUSE, $msg);
			}
			$po->addComments(Comments::TYPE_WAREHOUSE, $msg);
			
			$totalCount = PurchaseOrderItem::countByCriteria('purchaseOrderId = ? and receivedQty < qty', array($po->getId()));
			if($totalCount === 0)
				$po->setStatus(PurchaseOrder::STATUS_RECEIVING)->save()->addComments(Comments::TYPE_WAREHOUSE, '')->addLog(Log::TYPE_SYSTEM, '', __CLASS__ . '::' . __FUNCTION__);
			
			
// 			$supplier = Supplier::get(trim($param->CallbackParameter->supplier->id));
// 			$purchaseOrderId = trim($param->CallbackParameter->id);
// 			if(!$supplier instanceof Supplier)
// 				throw new Exception('Invalid Supplier passed in!');
// 			$supplierRefNum = trim($param->CallbackParameter->supplierRefNum);
// 			$supplierContactName = trim($param->CallbackParameter->contactName);
// 			$supplierContactNo = trim($param->CallbackParameter->contactNo);
// 			$shippingCost = trim($param->CallbackParameter->shippingCost);
// 			$handlingCost = trim($param->CallbackParameter->handlingCost);
// 			$comment = trim($param->CallbackParameter->comments);
// 			$status = trim($param->CallbackParameter->status);
// 			$purchaseOrder = PurchaseOrder::get($purchaseOrderId);
// 			$purchaseOrderTotalAmount = trim($param->CallbackParameter->totalAmount);
// 			$purchaseOrderTotalPaid = trim($param->CallbackParameter->totalPaid);
// 			$purchaseOrder->setTotalAmount($purchaseOrderTotalAmount)
// 			->setTotalPaid($purchaseOrderTotalPaid)
// 			->setSupplierRefNo($supplierRefNum)
// 			->setSupplierContact($supplierContactName)
// 			->setSupplierContactNumber($supplierContactNo)
// 			->setshippingCost($shippingCost)
// 			->sethandlingCost($handlingCost)
// 			->setStatus($status)
// 			->save();
// 			$purchaseOrder->addComment($comment, Comments::TYPE_SYSTEM);
// 			foreach ($param->CallbackParameter->newItems as $item) {
// 				$productId = trim($item->product->id);
// 				$productUnitPrice = trim($item->unitPrice);
// 				$qtyOrdered = trim($item->qtyOrdered);
// 				$productWtyOrdered = trim($item->qtyOrdered);
// 				$productTotalPrice = trim($item->totalPrice);
// 				$product = Product::get($productId);
// 				if(!$product instanceof Product)
// 					throw new Exception('Invalid Product passed in!');
// 				$purchaseOrder->addItem($product,$supplier->getId(),$productUnitPrice,$qtyOrdered,'','',$productTotalPrice) -> save();
// 			};
// 			foreach ($param->CallbackParameter->removedOldItems as $item) {
// 				$productId = trim($item->product->id);
// 				$productUnitPrice = trim($item->unitPrice);
// 				$qtyOrdered = trim($item->qtyOrdered);
// 				$productWtyOrdered = trim($item->qtyOrdered);
// 				$productTotalPrice = trim($item->totalPrice);
// 				$product = Product::get($productId);
// 				if(!$product instanceof Product)
// 					throw new Exception('Invalid Product passed in!');
// 				$removedItemPOitem = PurchaseOrderItem::getAllByCriteria('purchaseOrderId = ? and productId = ?',array($purchaseOrder-> getId(), $product->getId()),true,1,1)[0];
// 				$removedItemPOitem->setActive(false)->save();
// 			};
// 			$results['item'] = $purchaseOrder->getJson();
// 			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>