<?php
/**
 * This is the OrderController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class PriceBulkloadController extends BPCPageAbstract
{
	public $orderPageSize = 10;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'bulkload';
	
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
		$js = parent::_getEndJs();
		
		$compnayArray = json_encode(array_keys(Config::getCompnyListForPriceMatching()));
		
		// Setup the dnd listeners.
		$js .= 'pageJs.dropShowDiv.dropDiv = "drop_file";';
		$js .= 'pageJs.dropShowDiv.showDiv = "show_file";';
		$js .= 'pageJs.dropShowDiv.resultDiv = "price_compare";';
		$js .= 'pageJs.companyNameArray = '.json_encode(array_keys(Config::getCompnyListForPriceMatching())).';';
		$js .= 'pageJs.csvSeperator = ",";';
		//$js .= 'pageJs.intializeFileReader();';
		$js .= 'pageJs.setCallbackId("getAllPricesForProduct", "' . $this->getAllPricesForProductBtn->getUniqueID() . '");';
		$js .= 'pageJs.initializeFileHandler();';
		return $js;
	}
	
	public function getAllPricesForProduct($sender, $param)
	{
		$result = $errors = $outputArray = $finalOutputArray = array();
		try
		{
			$sku = $minPrice = '';
			$myPrice = 0;
			
			if(isset($param->CallbackParameter->sku) && trim($param->CallbackParameter->sku) !== '')
				$sku = trim($param->CallbackParameter->sku);
			if(isset($param->CallbackParameter->price) && trim($param->CallbackParameter->price) !== '')
				$myPrice = trim($param->CallbackParameter->price);
			
			if($sku !== '')
			{	
				$productPriceArray = HTMLParser::getPriceListForProduct($sku);
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
					foreach(Config::getCompnyListForPriceMatching() as $key => $value)
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
						$tmp['priceURL'] = $pp['priceLink'];
						$tmp['companyKey'] = $key;
						$tmp['company'] = $key;
						$outputArray[] = $tmp;
					}
				}
				
				if($myPrice !== 0)
				{
					if(($minPrice !== '' && $myPrice < $minPrice) || ($minPrice === ''))
						$minPrice = $myPrice;
				}	
				else
				{
					if($minPrice === '')
						$minPrice = 0.00;
				}

				/// here we are actually serializing the price /// 
				$fOutputArray = array();
				
				foreach(Config::getCompnyListForPriceMatching() as $key => $value)
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
						$fOutputArray[] = array('price' => '$0.00', 'priceURL' => '', 'company' => $key);
				}
			}
			
			$finalOutputArray['sku'] = $sku;
			$finalOutputArray['minPrice'] = $minPrice;
			$finalOutputArray['myPrice'] = $myPrice;
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