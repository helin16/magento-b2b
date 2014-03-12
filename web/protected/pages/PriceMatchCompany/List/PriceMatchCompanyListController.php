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
		$js .= 'pageJs.setCallbackId("getPriceMatchCompany", "' . $this->getPriceMatchCompanyBtn->getUniqueID(). '");';
		$js .= 'pageJs.displayAllPriceMatchCompany();';
		
		return $js;
	}
	
	public function getPriceMatchCompanyDetails($sender, $param)
	{
		$result = $error = $pmcArray = $outputArray = $finalOutputArray = array();
		$pageNo = 1;
		$pageSize = 30;
		$searchCriteria = '';
		
		try 
		{
			if(isset($param->CallbackParameter->searchCriteria))
				$searchCriteria = trim($param->CallbackParameter->searchCriteria);
			
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}	
			
			if($searchCriteria === '')
				$pmcArray = PriceMatchCompany::findAll(true, $pageNo, $pageSize);
			else
				$pmcArray = FactoryAbastract::service('PriceMatchCompany')->findByCriteria('companyName like ?', array('%'.$searchCriteria.'%'));
	
			foreach($pmcArray as $pmc)
			{
				$companyName = trim($pmc->getCompanyName());
				$compnayAlias = trim($pmc->getCompanyAlias());
				
				if(!isset($outputArray[$companyName]))
					$outputArray[$companyName] = array();
				
				$outputArray[$companyName][] = array('id' => $pmc->getId(), 'alias' => $compnayAlias);
			}
			
			foreach($outputArray as $key => $value)
			{
				$tmp = array();
				$tmp['companyName'] = $key;
				$tmp['companyAliases'] = $value;
				$finalOutputArray[] = $tmp;
			}
			
			$result['items'] = $finalOutputArray;
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
		
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
}
