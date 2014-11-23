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
		$statusOptions =  $purchaseOrder->getStatusOptions();
// 		var_dump($products[0]->getProduct());die;
// 		var_dump($products);die;
		$purchaseOrderItems = array();
		foreach (PurchaseOrderItem::getAllByCriteria('purchaseOrderId = ?', array($purchaseOrder->getId()), true, 1, DaoQuery::DEFAUTL_PAGE_SIZE, array('po_item.id'=>'asc')) as $item) {
			$product = $item->getProduct();
			if(!$product instanceof Product)
				throw new Exception('Invalid Product passed in!');
			$unitPrice = $item->getUnitPrice();
			$qty = $item->getQty();
			$totalPrice = $item->getTotalPrice();
			array_push($purchaseOrderItems,array('product'=> $product, 'unitPrice'=> $unitPrice, 'qrt'=> $qty, 'totalPrice'=> $totalPrice));
		};
		$js = parent::_getEndJs();
		$js .= "pageJs.setPreData(" . json_encode($purchaseOrder->getJson()) . ")"; 
		$js .= ".setStatusOptions(" . json_encode($statusOptions) . ")";
		$js .= ".setPurchaseOrderItems(" . json_encode($purchaseOrderItems) . ")";
		$js .= ".load()";
		$js .= ".bindAllEventNObjects();";
		return $js;
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
