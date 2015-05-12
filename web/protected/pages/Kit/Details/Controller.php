<?php
/**
 * This is the Task details page
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends DetailsPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'tasks.details';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEntityName
	 */
	protected $_focusEntity = 'Task';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
// 		if(!AccessControl::canAccessPurcahseOrdersPage(Core::getRole()))
// 			die('You do NOT have access to this page');
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$class = $this->_focusEntity;
		$js = parent::_getEndJs();

		$statusArray = array_map(create_function('$a', 'return $a->getJson();'), TaskStatus::getAll());
		$js .= "pageJs";
		$js .= ".load()";
		$js .= ";";
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
			$supplierId = trim($param->CallbackParameter->supplierId);
			$supplier = Supplier::get($supplierId);
			$orderDate = trim($param->CallbackParameter->orderDate);
			$totalAmount = trim($param->CallbackParameter->totalAmount);
			$totalPaid = trim($param->CallbackParameter->totalPaid);

			if(isset($param->CallbackParameter->id)) {
				$perchaseorder->setPurchaseOrderNo($purchaseOrderNo)
					->setSupplier($supplier)
					->setSupplierRefNo($supplierId)
					->setOrderDate($orderDate)
					->setTotalAmount($totalAmount)
					->setTotalPaid($totalPaid)
					->save();
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
