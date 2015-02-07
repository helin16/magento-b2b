<?php

/**
 * This is the listing page for payment method
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ListController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'paymentmethod';
	protected $_focusEntity = 'PaymentMethod';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
// 		if(!AccessControl::canAccessProductsPage(Core::getRole()))
// 			die('You do NOT have access to this page');
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs.setCallbackId('genReportBtn', '" . $this->genReportBtn->getUniqueID() . "')";
		$js .= ".bindBtns();";
		return $js;
	}
	public function genReport($sender, $params)
	{
		$results = $errors = array();
		try {
			Dao::beginTransaction();
			if(isset($param->CallbackParameter->type) && ($type = trim($param->CallbackParameter->type)) === '')
				throw new Exception('SYSTEM ERROR: invalid type passed in.');
			switch(strtolower($type)) {
				case 'sales_daily': {
					SalesExport_Xero::run();
					break;
				}
				case 'supplier_bill_daily':  {
					BillExport_Xero::run();
					break;
				}
				case 'manual_journal': {
					ManualJournalExport_Xero::run();
					break;
				}
				case 'item_list': {
					ItemExport_Xero::run();
					break;
				}
				default: {
					throw new Exception('SYSTEM ERROR: invalid type passed in: ' . $type);
				}
			}
			Dao::commitTransaction();
		} catch(Exception $ex) {
			Dao::rollbackTransaction();
			$error[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
