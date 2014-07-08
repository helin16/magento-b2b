<?php
class PriceMatchCompanyListController extends BPCPageAbstract
{
	public $menuItem = 'priceMatchCompany';
	/**
	 * constructor
	 */
	public function __construct()
	{
		if(!AccessControl::canAccessPriceMatchPage(Core::getRole()))
			die('You have no access to this page!');
		parent::__construct();
	}
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$pageSize = 30;
		$js .= 'pageJs._resultDivId = "resultDiv";';
		$js .= 'pageJs._pageInfo.pageSize = ' . $pageSize . ';';
		$js .= 'pageJs.setCallbackId("getPriceMatchCompany", "' . $this->getPriceMatchCompanyBtn->getUniqueID(). '")';
		$js .= '.setCallbackId("updatePriceMatchCompany", "' . $this->updatePriceMatchCompanyBtn->getUniqueID(). '")';
		$js .= '.setCallbackId("deleteAliasForPriceMatchCompany", "' . $this->deleteAliasForPriceMatchCompanyBtn->getUniqueID(). '")';
		$js .= '.displayAllPriceMatchCompany();';
		return $js;
	}
	/**
	 * Getting the company & company aliases list
	 * 
	 * @param mixed $sender
	 * @param mixed $param
	 * 
	 * @return PriceMatchCompanyListController
	 */
	public function getPriceMatchCompanyDetails($sender, $param)
	{
		$result = $error = $pmcArray = $outputArray = $finalOutputArray = array();
		try 
		{
			$searchCriteria = (isset($param->CallbackParameter->searchCriteria) ? trim($param->CallbackParameter->searchCriteria) : '');
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}	
			if($searchCriteria === '')
				$pmcArray = PriceMatchCompany::findAll(true, $pageNo, $pageSize, array('companyName' => 'asc'));
			else
				$pmcArray = FactoryAbastract::service('PriceMatchCompany')->findByCriteria('companyName like ?', array('%'.$searchCriteria.'%'), true, $pageNo, $pageSize, array('companyName' => 'asc'));
	
			foreach($pmcArray as $pmc)
			{
				$companyName = trim($pmc->getCompanyName());
				$compnayAlias = trim($pmc->getCompanyAlias());
				if(!isset($outputArray[$companyName]))
					$outputArray[$companyName] = array();
				$outputArray[$companyName][] = $pmc->getJson();
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
		return $this;
	}
	/**
	 * Updating the companyAlias details
	 * 
	 * @param mixed $sender
	 * @param mixed $param
	 * 
	 * @throws Exception
	 * @return PriceMatchCompanyListController
	 */
	public function updatePriceMatchCompanyDetails($sender, $param)
	{
		$result = $error = array();
		try
		{
			if(!isset($param->CallbackParameter->newAliasValue) || ($newAliasValue = trim($param->CallbackParameter->newAliasValue)) === '')
				throw new Exception('System Error: Price Match Company alias value NOT SET. Cannot be EMPTY');
			if(!isset($param->CallbackParameter->companyName) || ($companyName = trim($param->CallbackParameter->companyName)) === '')
				throw new Exception('System Error: Price Match Company Name value NOT SET. Cannot be EMPTY');
			
			$priceMatchCompany = (($pmcId = trim($param->CallbackParameter->id)) === '') ? new PriceMatchCompany() : FactoryAbastract::service('PriceMatchCompany')->get($pmcId);
			if(!$priceMatchCompany instanceof PriceMatchCompany)
				throw new Exception('System Error: Price Match Company Id(=' . $pmcId . ') is REQUIRED. Cannot be EMPTY/INVALID');
			
			$priceMatchCompany->setCompanyName($companyName);
			$priceMatchCompany->setCompanyAlias($newAliasValue);
			$priceMatchCompany = FactoryAbastract::service('PriceMatchCompany')->save($priceMatchCompany);
				
			$result['item'] = $priceMatchCompany->getJson();
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
		return $this;
	}
	
	public function deleteAliasForPriceMatchCompany($sender, $param)
	{
		$result = $error = array();
	
		try
		{
			if(!isset($param->CallbackParameter->data))
				throw new Exception('System Error: Noting to Delete!!!');
			$data = $param->CallbackParameter->data;
			if(!isset($data->id) || !isset($data->companyAlias) || ($id = trim($data->id)) == '' || ($alias = trim($data->companyAlias)) == '')
				throw new Exception('Data to be deleted is NOT proper format');
			
			$pmc = FactoryAbastract::service('PriceMatchCompany')->get($id);
			if(!$pmc instanceof PriceMatchCompany)
				throw new Exception('Invalid Id ['.$id.'] provided for PriceMatchCompany!!!');
			
			$pmc->setActive(false);
			$pmc = FactoryAbastract::service('PriceMatchCompany')->save($pmc);
			$result['items'] = $pmc->getJson();
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
}
