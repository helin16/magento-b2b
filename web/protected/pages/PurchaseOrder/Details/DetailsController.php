<?php
/**
 * This is the Product details page
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class DetailsController extends DetailsPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'purchaseorders.details';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEntityName
	 */
	protected $_focusEntity = 'PurchaseOrder';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(!AccessControl::canAccessProductsPage(Core::getRole()))
			die('You do NOT have access to this page');
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
		if(trim($this->Request['id']) === 'new')
			$purchaseOrder = new PurchaseOrder();
		else if(!($purchaseOrder = PurchaseOrder::get($this->Request['id'])) instanceof PurchaseOrder)
			die('Invalid Purchase Order!');
		$comments = array();
		foreach ($purchaseOrder->getComment() as $item) {
			array_push($comments, $item->getJson());
		};
		$statusOptions =  $purchaseOrder->getStatusOptions();
		$purchaseOrderItems = array();
		foreach (PurchaseOrderItem::getAllByCriteria('purchaseOrderId = ?', array($purchaseOrder->getId()), true, 1, DaoQuery::DEFAUTL_PAGE_SIZE, array('po_item.id'=>'asc')) as $item) {
			$product = Product::get($item->getProduct()->getId());
			if(!$product instanceof Product)
				throw new Exception('Invalid Product passed in!');
			$unitPrice = $item->getUnitPrice();
			$qty = $item->getQty();
			$totalPrice = $item->getTotalPrice();
			array_push($purchaseOrderItems,array('product'=> $product->getJson(), 'unitPrice'=> $unitPrice, 'qrt'=> $qty, 'totalPrice'=> $totalPrice));
		};
		$js = parent::_getEndJs();
		$js .= "pageJs.setPreData(" . json_encode($purchaseOrder->getJson()) . ")"; 
		$js .= ".setComment(" . json_encode($comments) . ")";
		$js .= ".setStatusOptions(" . json_encode($statusOptions) . ")";
		$js .= ".setCallbackId('searchProduct', '" . $this->searchProductBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('saveOrder', '" . $this->saveOrderBtn->getUniqueID() . "')";
		$js .= ".setPurchaseOrderItems(" . json_encode($purchaseOrderItems) . ")";
		$js .= ".load()";
		$js .= ".bindAllEventNObjects();";
		return $js;
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
			$supplierID = isset($param->CallbackParameter->supplierID) ? trim($param->CallbackParameter->supplierID) : '';
			$productIdsFromBarcode = array_map(create_function('$a', 'return $a->getProduct()->getId();'), ProductCode::getAllByCriteria('code = ?', array($searchTxt)));
			$where = (count($productIdsFromBarcode) === 0 ? '' : ' OR id in (' . implode(',', $productIdsFromBarcode) . ')');
			foreach(Product::getAllByCriteria('name like :searchTxt OR sku like :searchTxt' . $where, array('searchTxt' => $searchTxt . '%'), true, 1, DaoQuery::DEFAUTL_PAGE_SIZE) as $product)
			{
				$array = $product->getJson();
				
				$array['minProductPrice'] = 0;
				$array['lastSupplierPrice'] = 0;
				$array['minSupplierPrice'] = 0;
				
				$minProductPriceProduct = PurchaseOrderItem::getAllByCriteria('productId = ?', array($product->getId()), true, 1, 1, array('unitPrice'=> 'asc'));
				$minProductPrice = sizeof($minProductPriceProduct) ? $minProductPriceProduct[0]->getUnitPrice() : 0;
				$minProductPriceId = sizeof($minProductPriceProduct) ? $minProductPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				$lastSupplierPriceProduct = PurchaseOrderItem::getAllByCriteria('productId = ? and supplierId = ?', array($product->getId(), $supplierID), true, 1, 1, array('id'=> 'desc'));
				$lastSupplierPrice = sizeof($lastSupplierPriceProduct) ? $lastSupplierPriceProduct[0]->getUnitPrice() : 0;
				$lastSupplierPriceId = sizeof($lastSupplierPriceProduct) ? $lastSupplierPriceProduct[0]->getPurchaseOrder()->getId() : '';
				
				$minSupplierPriceProduct = PurchaseOrderItem::getAllByCriteria('productId = ? and supplierId = ?', array($product->getId(), $supplierID), true, 1, 1, array('unitPrice'=> 'asc'));
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
			$supplier = Supplier::get(trim($param->CallbackParameter->supplier->id));
			$purchaseOrderId = trim($param->CallbackParameter->id);
			if(!$supplier instanceof Supplier)
				throw new Exception('Invalid Supplier passed in!');
			$supplierRefNum = trim($param->CallbackParameter->supplierRefNum);
			$supplierContactName = trim($param->CallbackParameter->contactName);
			$supplierContactNo = trim($param->CallbackParameter->contactNo);
			$shippingCost = trim($param->CallbackParameter->shippingCost);
			$handlingCost = trim($param->CallbackParameter->handlingCost);
			$comment = trim($param->CallbackParameter->comments);
			$status = trim($param->CallbackParameter->status);
			$purchaseOrder = PurchaseOrder::get($purchaseOrderId);
			$purchaseOrderTotalAmount = trim($param->CallbackParameter->totalAmount);
			$purchaseOrderTotalPaid = trim($param->CallbackParameter->totalPaid);
			$purchaseOrder->setTotalAmount($purchaseOrderTotalAmount)
				->setTotalPaid($purchaseOrderTotalPaid)
				->setSupplierRefNo($supplierRefNum)
				->setSupplierContact($supplierContactName)
				->setSupplierContactNumber($supplierContactNo)
				->setshippingCost($shippingCost)
				->sethandlingCost($handlingCost)
				->setStatus($status)
				->save();
			$purchaseOrder->addComment($comment, Comments::TYPE_SYSTEM);
			foreach ($param->CallbackParameter->newItems as $item) {
				$productId = trim($item->product->id);
				$productUnitPrice = trim($item->unitPrice);
				$qtyOrdered = trim($item->qtyOrdered);
				$productWtyOrdered = trim($item->qtyOrdered);
				$productTotalPrice = trim($item->totalPrice);
				$product = Product::get($productId);
				if(!$product instanceof Product)
					throw new Exception('Invalid Product passed in!');
				$purchaseOrder->addItem($product,$supplier->getId(),$productUnitPrice,$qtyOrdered,'','',$productTotalPrice) -> save();
			};
			foreach ($param->CallbackParameter->removedOldItems as $item) {
				$productId = trim($item->product->id);
				$productUnitPrice = trim($item->unitPrice);
				$qtyOrdered = trim($item->qtyOrdered);
				$productWtyOrdered = trim($item->qtyOrdered);
				$productTotalPrice = trim($item->totalPrice);
				$product = Product::get($productId);
				if(!$product instanceof Product)
					throw new Exception('Invalid Product passed in!');
				$removedItemPOitem = PurchaseOrderItem::getAllByCriteria('purchaseOrderId = ? and productId = ?',array($purchaseOrder-> getId(), $product->getId()),true,1,1)[0];
				$removedItemPOitem->setActive(false)->save();
			};
			$results['item'] = $purchaseOrder->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * (non-PHPdoc)
	 * @see DetailsPageAbstract::saveItem()
	 */
	public function saveItem($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			$perchaseorder = !isset($param->CallbackParameter->id) ? new PurchaseOrder() : PurchaseOrder::get(trim($param->CallbackParameter->id));
			if(!$perchaseorder instanceof PurchaseOrder)
				throw new Exception('Invalid Purchase Order passed in!');
			$purchaseOrderNo = trim($param->CallbackParameter->purchaseOrderNo);
			$id = !isset($param->CallbackParameter->id) ? '' : trim($param->CallbackParameter->id);
			$active = !is_numeric($param->CallbackParameter->id) ? '' : trim($param->CallbackParameter->active);
			$supplieName = !is_numeric($param->CallbackParameter->supplierName) ? '' : trim($param->CallbackParameter->supplierName);
			$supplierId = trim($param->CallbackParameter->supplierId);
			$supplier = Supplier::get($supplierId);
			$supplierContactName = trim($param->CallbackParameter->supplierContactName);
			$orderDate = trim($param->CallbackParameter->orderDate);
			$totalAmount = trim($param->CallbackParameter->totalAmount);
			$totalPaid = trim($param->CallbackParameter->totalPaid);
			
			if(isset($param->CallbackParameter->id)) {
			$perchaseorder->setPurchaseOrderNo($purchaseOrderNo)
				->setSupplier($supplier)
				->setSupplierRefNo($supplierId)
// 				->setSupplierContact($supplierContactName)
				->setOrderDate($orderDate)
				->setTotalAmount($totalAmount)
				->setTotalPaid($totalPaid)
			;
// 			if(trim($perchaseorder->getId()) === '')
// 				$perchaseorder->setIsFromB2B(false);
			$perchaseorder->save();
			} else {
// 				PurchaseOrder::
			}
			
			$results['url'] = '/purchase/' . $perchaseorder->getId() . '.html';
			$results['item'] = $perchaseorder->getJson();
			
			Dao::commitTransaction();

		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage() . $ex->getTraceAsString();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
