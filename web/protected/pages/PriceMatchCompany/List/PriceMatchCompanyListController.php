<?php
class PriceMatchCompanyListController extends BPCPageAbstract
{
	public $menuItem = 'priceMatchCompany';
	
	public function __construct()
	{
		if(!AccessControl::canAccessUsersPage(Core::getRole()))
			die('You have no access to this page!');
		parent::__construct();
	}
	
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$pageNo = 1;
		$pageSize = 30;
		
		$js .= 'pageJs._resultDivId = "resultDiv";';
		$js .= 'pageJs._pageInfo.pageNo = '.$pageNo.';';
		$js .= 'pageJs._pageInfo.pageSize = '.$pageSize.';';
		$js .= 'pageJs._resultDivId = "resultDiv";';
		$js .= 'pageJs.setCallbackId("getPriceMatchCompany", "' . $this->getPriceMatchCompanyBtn->getUniqueID(). '")';
		$js .= 'pageJs.displayAllPriceMatchCompany();';
		
		return $js;
	}
	
	public function getPriceMatchCompanyDetails($sender, $param)
	{
		$result = $error = $pmcArray = array();
		
		if(!isset($param->CallbackParameter->searchCriteria))
			$searchCriteria = '';
		else 
			$searchCriteria = trim($param->CallbackParameter->searchCriteria);
		
		$pageNo = 1;
		$pageSize = 30;
		if(isset($param->CallbackParameter->pagination))
		{
			$pageNo = $param->CallbackParameter->pagination->pageNo;
			$pageSize = $param->CallbackParameter->pagination->pageSize;
		}	
		
		if($searchCriteria === '')
			$pmcArray = PriceMatchCompany::findAll(true, $pageNo, $pageSize);
		
				
		
		
		
	}
}
