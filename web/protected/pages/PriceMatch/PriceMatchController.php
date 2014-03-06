<?php
/**
 * This is the PriceMatchController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class PriceMatchController extends BPCPageAbstract
{
	public $orderPageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'priceMatch';
	
	private $_companyListForPriceMatch;
	
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
	}
	
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$companyArray = array_keys($this->_getAllCompaniesForPriceMatching());
		if(count($companyArray) === 0)
			die('No Price Match Company Set up. Please set up the Price Matching companies before using this page!!!!!');
		
		$js = parent::_getEndJs();
		// Setup the dnd listeners.
		$js .= 'pageJs.dropShowDiv.dropDiv = "drop_file";';
		$js .= 'pageJs.dropShowDiv.showDiv = "show_file";';
		$js .= 'pageJs.dropShowDiv.resultDiv = "price_compare";';
		$js .= 'pageJs.companyNameArray = '.json_encode($companyArray).';';
		$js .= 'pageJs.csvSeperator = ",";';
		//$js .= 'pageJs.intializeFileReader();';
		$js .= 'pageJs.setCallbackId("getAllPricesForProduct", "' . $this->getAllPricesForProductBtn->getUniqueID() . '");';
		$js .= 'pageJs.initializeFileHandler();';
		return $js;
	}
	
	private function _getAllCompaniesForPriceMatching()
	{
		if(is_array($this->_companyListForPriceMatch) && count($this->_companyListForPriceMatch) > 0)
			return $this->_companyListForPriceMatch;
		
		$outputArray = array();
		foreach(PriceMatchCompany::findAll() as $pmc)
		{
			$companyName = trim($pmc->getCompanyName());
			if(!isset($outputArray[$companyName]))
				$outputArray[$companyName] = array();
			
			$outputArray[$companyName][] = trim($pmc->getCompanyAlias());
		}	
		$this->_companyListForPriceMatch = $outputArray;
		
		return $this->_companyListForPriceMatch;	
	}
	
	public function getAllPricesForProduct($sender, $param)
	{
		$result = $errors = $outputArray = $finalOutputArray = array();
		try
		{
			$sku = $minPrice = $priceDiff = $url = '';
			$fOutputArray = array();
			$myPrice = 0;
			
			if(isset($param->CallbackParameter->sku) && trim($param->CallbackParameter->sku) !== '')
				$sku = trim($param->CallbackParameter->sku);
			if(isset($param->CallbackParameter->price) && trim($param->CallbackParameter->price) !== '')
			{	
				$myPrice = trim($param->CallbackParameter->price);
				if(preg_match('/^\$/', $myPrice))
					$myPrice = substr($myPrice, 1);
			}
			
			if($sku !== '')
			{	
				$productPriceArray = HTMLParser::getPriceListForProduct($sku, $url);
				foreach($productPriceArray as $pp)
				{
					$companyURL = '';
					if(($companyDetails = trim($pp['companyDetails'])) !== '')
					{
						$cdArray = explode('|', $companyDetails);
						if(isset($cdArray[count($cdArray) - 2]))
							$companyURL = trim($cdArray[count($cdArray) - 2]);
						else
							$companyURL =  $companyDetails;
					}
					
					$insert = false;
					foreach($this->_getAllCompaniesForPriceMatching() as $key => $value)
					{
						$newValueArray = array_map(create_function('$a', 'return strtolower($a);'), $value);
						if(in_array(strtolower($companyURL), $newValueArray))
						{	
							$insert = true;
							break;
						}	
					}	
					
					if($insert === true)
					{
						$tmp = array();
						$currentPrice = $pp['price'];
						if(preg_match('/^\$/', $currentPrice))
							$currentPrice = substr($currentPrice, 1);
							
						if($minPrice === '' || $currentPrice < $minPrice)
							$minPrice = $currentPrice;
							
						$tmp['price'] = $pp['price'];
						$tmp['priceURL'] = HTMLParser::getHostUrl() . $pp['priceLink'];
						$tmp['companyKey'] = $key;
						$tmp['company'] = $key;
						$outputArray[] = $tmp;
					}
				}
				
				if($myPrice !== 0)
				{
					if($minPrice !== '')
						$priceDiff = ($myPrice - $minPrice);
					else
						$minPrice = '0.00';
				}	
				else
				{
					$myPrice = '0.00';
					if($minPrice === '')
						$minPrice = '0.00';
				}

				/// here we are actually serializing the price /// 
				
				
				foreach($this->_getAllCompaniesForPriceMatching() as $key => $value)
				{
					$keyFound = false;
					foreach($outputArray as $priceInfo)
					{
						if($priceInfo['companyKey'] === $key)
						{	
							$keyFound = true;
							$fOutputArray[] = $priceInfo;
							break;
						}
					}
					if($keyFound === false)
						$fOutputArray[] = array('price' => '$0.00', 'priceURL' => '', 'company' => $key, 'companyKey' => $key);
				}
			}
			
			$finalOutputArray['sku'] = $sku;
			$finalOutputArray['searchURL'] = $url;
			$finalOutputArray['minPrice'] = $minPrice;
			$finalOutputArray['myPrice'] = $myPrice;
			$finalOutputArray['priceDiff'] = $priceDiff;
			$finalOutputArray['data'] = $fOutputArray;
			
			$result['items'] = $finalOutputArray;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}
?>