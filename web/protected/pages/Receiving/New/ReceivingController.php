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
				PurchaseOrder::getQuery()->eagerLoad('PurchaseOrder.supplier');
				$pos = PurchaseOrder::getAllByCriteria('(po.purchaseOrderNo like :searchTxt OR po.supplierRefNo like :searchTxt OR po_sup.name like :suplierName) AND (status = :statusReceiving OR status = :statusOrdered)', array('searchTxt' => $searchTxt . '%', 'suplierName' => '%' . $searchTxt . '%', 'statusReceiving' => PurchaseOrder::STATUS_RECEIVING, 'statusOrdered' => PurchaseOrder::STATUS_ORDERED), true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('id'=> 'desc'));
				foreach($pos as $po)
				{
					if(!$po instanceof PurchaseOrder)
						throw new Exception('Invalid PurchaseOrder passed in!');
					$array = $po->getJson();
					$array['totalProdcutCount'] = $po->gettotalProdcutCount();
					
					$array['purchaseOrderItem'] = [];
					foreach (PurchaseOrderItem::getAllByCriteria('po_item.purchaseOrderId = :purchaseOrderId', array('purchaseOrderId'=> $po->getId() ), true, 1, DaoQuery::DEFAUTL_PAGE_SIZE * 10) as $purchaseOrderItem) 
					{
						$array['purchaseOrderItem'][] = array('purchaseOrderItem'=> $purchaseOrderItem->getJson(), 'product'=> $purchaseOrderItem->getProduct()->getJson());
					}
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
			$purchaseOrder = PurchaseOrder::get(trim($param->CallbackParameter->purchaseOrder->id));
			if(!$purchaseOrder instanceof PurchaseOrder)
				throw new Exception('Invalid PurchaseOrder passed in!');
			$product = Product::get(trim($param->CallbackParameter->product->id));
			if(!$product instanceof Product)
				throw new Exception('Invalid Product passed in!');
			$results['count'] = PurchaseOrderItem::countByCriteria('purchaseOrderId = :purchaseOrderId and productId = :productId', array('purchaseOrderId' => $purchaseOrder->getId(), 'productId' => $product->getId()));
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
			Product::getQuery()->eagerLoad('Product.codes', 'left join');
			$products = Product::getAllByCriteria($where, $params, true, 1, DaoQuery::DEFAUTL_PAGE_SIZE, array('pro.sku' => 'asc'));
			
			foreach($products as $product)
			{
				if(!$product instanceof Product)
					throw new Exception('Invalid Product passed in!');
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
			$items = array();
			$purchaseOrder = PurchaseOrder::get(trim($param->CallbackParameter->purchaseOrder->id));
			if(!$purchaseOrder instanceof PurchaseOrder)
				throw new Exception('Invalid PurchaseOrder passed in!');
			$comment = trim($param->CallbackParameter->comments);
			$purchaseOrder->addComment(Comments::TYPE_WAREHOUSE, $comment);
			$products = $param->CallbackParameter->products;
			
			foreach ($products->matched as $item) {
				$product = Product::get(trim($item->product->id));
				if(isset($item->product->EANcode) ) {
					$EANcode = trim($item->product->EANcode);
					$productcodes = ProductCode::getAllByCriteria('pro_code.productId = :code and pro_code.typeId = :typeId', array('code'=> $EANcode, 'typeId'=> ProductCodeType::ID_EAN), true, 1, 1);
					if(sizeof($productcodes)) {
						if(!$productcodes[0] instanceof ProductCode)
							throw new Exception('Invalid ProductCode passed in!');
						$productcodes[0]->setCode($EANcode);
					} else {
						ProductCode::create($product, ProductCodeType::get(ProductCodeType::ID_EAN), $EANcode);
					}
				}
				if(isset($item->product->UPCcode) ) {
					$UPCcode = trim($item->product->UPCcode);
					$productcodes = ProductCode::getAllByCriteria('pro_code.productId = :code and pro_code.typeId = :typeId', array('code'=> $EANcode, 'typeId'=> ProductCodeType::ID_EAN), true, 1, 1);
					if(sizeof($productcodes)) {
						if(!$productcodes[0] instanceof ProductCode)
							throw new Exception('Invalid ProductCode passed in!');
						$productcodes[0]->setCode($EANcode);
					} else {
						ProductCode::create($product, ProductCodeType::get(ProductCodeType::ID_EAN), $EANcode);
					}
				}
				
				if(!$product instanceof Product)
					throw new Exception('Invalid Product passed in!');
				
				$serials = $item->serial;
				foreach ($serials as $serial) {
					$serialNo = trim($serial->serialNo);
					$unitPrice = trim($serial->unitPrice);
					$invoiceNo = trim($serial->invoiceNo);
					$comments = trim($serial->comments);
					ReceivingItem::create($purchaseOrder, $product, $unitPrice, $serialNo, $invoiceNo, $comments);
					
					$nofullReceivedItems = PurchaseOrderItem::getAllByCriteria('productId = ? and purchaseOrderId = ?', array($product->getId(), $purchaseOrder->getId()), true, 1, 1, array('po_item.receivedQty' => 'asc'));
					if(count($nofullReceivedItems) > 0) {
						$nofullReceivedItems[0]
						->setReceivedQty($nofullReceivedItems[0]->getReceivedQty() + 1)
						->save()
						->addLog(Log::TYPE_SYSTEM, ($msg = 'received a product(SKU=' . $product->getSku() . ') by ' . Core::getUser()->getPerson()->getFullName() . '@' . trim(new UDate()) . '(UTC)'), __CLASS__ . '::' . __FUNCTION__)
						->addComment(Comments::TYPE_WAREHOUSE, $msg);
					}
					
				}
				
				$purchaseOrder->addComment(Comments::TYPE_WAREHOUSE, 'received ' . count($serials) . ' product(SKU=' . $product->getSku() . ') by ' . Core::getUser()->getPerson()->getFullName() . '@' . trim(new UDate()) . '(UTC)');
			}
			
			$totalCount = PurchaseOrderItem::countByCriteria('active = 1 and purchaseOrderId = ? and receivedQty < qty', array($purchaseOrder->getId()));
			if(trim($totalCount) === '0')
			{
				$purchaseOrder->setStatus(PurchaseOrder::STATUS_RECEIVED);
			}
			else
			{
				$purchaseOrder->setStatus(PurchaseOrder::STATUS_RECEIVING);
			}
			$purchaseOrder->save();
			
			$results['item'] = $purchaseOrder->getJson();
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