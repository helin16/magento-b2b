<?php
class PriceMatchCompanyListController extends BPCPageAbstract
{
	public $menuItem = 'priceMatchCompany';
	
	public function __construct()
	{
		if(!AccessControl::canAccessPriceMatchPage(Core::getRole()))
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
		$js .= 'pageJs._addCompanyDivId = "addCompanyDiv";';
		$js .= 'pageJs._pageInfo.pageNo = '.$pageNo.';';
		$js .= 'pageJs._pageInfo.pageSize = '.$pageSize.';';
		$js .= 'pageJs.setCallbackId("getPriceMatchCompany", "' . $this->getPriceMatchCompanyBtn->getUniqueID(). '");';
		$js .= 'pageJs.setCallbackId("updatePriceMatchCompany", "' . $this->updatePriceMatchCompanyBtn->getUniqueID(). '");';
		$js .= 'pageJs.setCallbackId("addAliasForPriceMatchCompany", "' . $this->addAliasForPriceMatchCompanyBtn->getUniqueID(). '");';
		$js .= 'pageJs.setCallbackId("deleteAliasForPriceMatchCompany", "' . $this->deleteAliasForPriceMatchCompanyBtn->getUniqueID(). '");';
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
	
	public function updatePriceMatchCompanyDetails($sender, $param)
	{
		$result = $error = array();
	
		try
		{
			if(!isset($param->CallbackParameter->id) || !isset($param->CallbackParameter->newAliasValue))
				throw new Exception('System Error: Price Match Company Id and new alias value NOT SET');
			if(($pmcId = trim($param->CallbackParameter->id)) === '' || !($priceMatchCompany = FactoryAbastract::service('PriceMatchCompany')->get($pmcId)) instanceof PriceMatchCompany)
				throw new Exception('System Error: Price Match Company Id is REQUIRED. Cannot be EMPTY/INVALID');
			if(($newAliasValue = trim($param->CallbackParameter->newAliasValue)) === '')
				throw new Exception('System Error: Price Match Company new alias value is REQUIRED. Cannot be EMPTY');
			
			$priceMatchCompany->setCompanyAlias($newAliasValue);
			$priceMatchCompany = FactoryAbastract::service('PriceMatchCompany')->save($priceMatchCompany);
				
			$result['items'] = $priceMatchCompany;
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
	
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
	
	public function deleteAliasForPriceMatchCompany($sender, $param)
	{
		$result = $error = array();
	
		try
		{
			if(!isset($param->CallbackParameter->data))
				throw new Exception('System Error: Noting to Delete!!!');
			$data = $param->CallbackParameter->data;
			if(!isset($data->id) || !isset($data->alias) || ($id = trim($data->id)) == '' || ($alias = trim($data->alias)) == '')
				throw new Exception('Data to be deleted is NOT proper format');
			
			$pmc = FactoryAbastract::service('PriceMatchCompany')->get($id);
			if(!$pmc instanceof PriceMatchCompany)
				throw new Exception('Invalid Id ['.$id.'] provided for PriceMatchCompany!!!');
			
			$pmc->setActive(false);
			$pmc = FactoryAbastract::service('PriceMatchCompany')->save($pmc);
	
			$result['items'] = $pmc;
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
	
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
	
	
	public function addAliasForPriceMatchCompany($sender, $param)
	{
		$result = $error = $pmcArray = array();
		
		try
		{
			if(!isset($param->CallbackParameter->aliasArray) || !isset($param->CallbackParameter->companyName))
				throw new Exception('System Error: New Alias value OR company name NOT SET');
			
			$newAliasValueArray = $param->CallbackParameter->aliasArray;
			if(!is_array($newAliasValueArray) || count($newAliasValueArray) === 0)
				throw new Exception('System Error: New Alias value(s) REQUIRED to set. Cannot be EMPTY/INVALID');
			
			if(($companyName = trim($param->CallbackParameter->companyName)) === '')
				throw new Exception('System Error: Company Name is REQUIRED. Cannot be EMPTY');

			$ePMCArray = FactoryAbastract::service('PriceMatchCompany')->findByCriteria('companyName = ? and companyAlias IN ('. implode(',', array_fill(0, count($newAliasValueArray), '?')) .')', array_merge(array($companyName), $newAliasValueArray));
			$eAliasArray = array_map(create_function('$a', 'return $a->getCompanyAlias();'), $ePMCArray);
			
			$newAliasValueArray = array_diff($newAliasValueArray, $eAliasArray);
			
			foreach($newAliasValueArray as $newAlias)
			{
				if(($newAlias = trim($newAlias)) !== '')
				{
					$pmc = new PriceMatchCompany();
					$pmc->setCompanyName($companyName)->setCompanyAlias($newAlias)->setActive(true);
					$pmcArray[] = FactoryAbastract::service('PriceMatchCompany')->save($pmc);
				}
			}	
			$result['items'] = $pmcArray;
		}
		catch(Exception $ex)
		{
			$error[] = $ex->getMessage();
		}
		
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
}
