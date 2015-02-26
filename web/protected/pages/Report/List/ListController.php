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
	public function genReport($sender, $param)
	{
		$results = $errors = array();
		try {
			Dao::beginTransaction();
			if(isset($param->CallbackParameter->type) && ($type = trim($param->CallbackParameter->type)) === '')
				throw new Exception('SYSTEM ERROR: invalid type passed in.');
			$asset = null;
			switch(strtolower($type)) {
				case 'sales_daily': {
					SalesExport_Xero::setStartNEndDate(new UDate(trim($param->CallbackParameter->date_from)), new UDate(trim($param->CallbackParameter->date_to)));
					$asset = SalesExport_Xero::run(false, false);
					break;
				}
				case 'supplier_bill_daily':  {
					BillExport_Xero::setStartNEndDate(new UDate(trim($param->CallbackParameter->date_from)), new UDate(trim($param->CallbackParameter->date_to)));
					$asset = BillExport_Xero::run(false, false);
					break;
				}
				case 'manual_journal': {
					ManualJournalExport_Xero::setStartNEndDate(new UDate(trim($param->CallbackParameter->date_from)), new UDate(trim($param->CallbackParameter->date_to)));
					$asset = ManualJournalExport_Xero::run(false, false);
					break;
				}
				case 'inventory_list': {
					$asset = ItemExport_Xero::run(false, false);
					break;
				}
				case 'magento_price': {
					ItemExport_Magento::setStartNEndDate(new UDate(trim($param->CallbackParameter->date_from)), new UDate(trim($param->CallbackParameter->date_to)));
					$asset = ItemExport_Magento::run(false, false);
					break;
				}
				default: {
					throw new Exception('SYSTEM ERROR: invalid type passed in: ' . $type);
				}
			}
			if($asset instanceof Asset)
				$results['item'] = $asset->getJson();
			Dao::commitTransaction();
		} catch(Exception $ex) {
			Dao::rollbackTransaction();
			$error[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
