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
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::onLoad()
	 */
	public function onLoad($param)
	{
		parent::onLoad($param);
		if(!AccessControl::canAccessPriceMatchPage(Core::getRole()))
			die(BPCPageAbstract::show404Page('Access Denied', 'You do NOT have the access to this page!'));
	}
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		$companyArray = $this->_getAllCompaniesForPriceMatching();
		if(count(array_keys($companyArray)) === 0)
			die('No Price Match Company Set up. Please set up the Price Matching companies before using this page!!!!!');
		
		$js = parent::_getEndJs();
		// Setup the dnd listeners.
		$js .= 'pageJs.setCallbackId("getAllPricesForProduct", "' . $this->getAllPricesForProductBtn->getUniqueID() . '")';
		$js .= '.load("price_match_div", ' . json_encode($companyArray) . ');';
		return $js;
	}
	/**
	 * Getting all the price match companies
	 * @return Ambigous <multitype:multitype: , string>
	 */
	private function _getAllCompaniesForPriceMatching()
	{
		$outputArray = array();
		foreach(PriceMatchCompany::findAll() as $pmc)
		{
			$companyName = trim($pmc->getCompanyName());
			if(!isset($outputArray[$companyName]))
				$outputArray[$companyName] = array();
				
			$outputArray[$companyName][] = trim($pmc->getCompanyAlias());
		}
		return $outputArray;	
	}
	
	public function getAllPricesForProduct($sender, $param)
	{
		$result = $errors = $outputArray = $finalOutputArray = array();
		try
		{
			if(!isset($param->CallbackParameter->sku) || ($sku = trim($param->CallbackParameter->sku)) === '')
				throw new Exception('No SKU to search on!');
			if(!isset($param->CallbackParameter->companyAliases) || count($companyAliases = json_decode(json_encode($param->CallbackParameter->companyAliases), true)) === 0)
				throw new Exception('No companyAliases to search on!');
			
			if(!isset($param->CallbackParameter->price) || ($myPrice = str_replace(' ', '', $param->CallbackParameter->price)) === '')
				$myPrice = 0;
			else if(!is_numeric($myPrice = str_replace('$', '', str_replace(',', '', $myPrice) )))
				throw new Exception('No provided my price is NOT a number(=' . $myPrice . '!');
			
			//initialize values
			$finalOutputArray = array(
				'sku'             => $sku
				,'myPrice'        => $myPrice
				,'minPrice'       => 0
				,'companyPrices'  => array()
			);
			foreach($companyAliases as $key => $value)
				$finalOutputArray['companyPrices'][$key] = array('price' => 0, 'priceURL' => '');
			
			//getting actual values
			$productPriceArray = HTMLParser::getPriceListForProduct($sku);
			foreach($productPriceArray as $productPriceInfo)
			{
				if(($companyDetails = trim($productPriceInfo['companyDetails'])) === '')
					continue;
				
				$cdArray = explode('|', $companyDetails);
				$companyURL = (isset($cdArray[count($cdArray) - 2])) ? trim($cdArray[count($cdArray) - 2]) : trim($companyDetails);
				
				foreach($companyAliases as $key => $value)
				{
					if(in_array(strtolower($companyURL), array_map(create_function('$a', 'return strtolower($a);'), $value)))
					{	
						$price = str_replace(' ', '', str_replace('$', '', str_replace(',', '', $productPriceInfo['price']) ) );
						if($finalOutputArray['minPrice'] == 0 || $finalOutputArray['minPrice'] > $price)
							$finalOutputArray['minPrice'] = $price;
						$finalOutputArray['companyPrices'][$key] = array(
								'price' => $price
								,'priceURL' => HTMLParser::getHostUrl() . $productPriceInfo['priceLink']
						);
						break;
					}	
				}	
			}
			
			//return the result
			$finalOutputArray['priceDiff'] = ($finalOutputArray['myPrice'] - $finalOutputArray['minPrice']);
			$result['item'] = $finalOutputArray;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $errors);
	}
}
?>