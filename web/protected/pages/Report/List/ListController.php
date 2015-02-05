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
		$js .= "pageJs.setCallbackId('salesReport', '" . $this->salesReportBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('billsReport', '" . $this->billsReportBtn->getUniqueID() . "')";
		$js .= ".bindBtns();";
		return $js;
	}
	public function salesReport($sender, $params)
	{
		SalesExport_Xero::run();
	}
	public function billsReport($sender, $params)
	{
		BillExport_Xero::run();
	}
}
?>
