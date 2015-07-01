<?php
/**
 * Global Price matching class
 *
 * @package    Core
 * @subpackage Utils
 * @author     lhe<helin16@gmail.com>
 */
abstract class PriceMatcher
{
	/**
	 * Getting all the price match companies
	 * @return Ambigous <multitype:multitype: , string>
	 */
	public static function getAllCompaniesForPriceMatching()
	{
		$outputArray = array();
		foreach(PriceMatchCompany::findAll() as $pmc)
		{
			$companyName = trim($pmc->getCompanyName());
			if(!isset($outputArray[$companyName]))
			{
				$outputArray[$companyName] = array();
			}
			
			$outputArray[$companyName][] = trim($pmc->getCompanyAlias());
			$outputArray[$companyName]['PriceMatchCompanyId'] = $pmc->getId(); // added b/c improvment of pricematch (Jun2015). it keeps PriceMatchCompany for further reference
		}
		return $outputArray;
	}
	/**
	 * Getting the price match result
	 * 
	 * @param array  $companyAliases
	 * @param string $sku
	 * @param number $myPrice
	 * 
	 * @return multitype:number multitype: unknown Ambigous <number, mixed>
	 */
	public static function getPrices($companyAliases, $sku, $myPrice)
	{
		$myPrice = StringUtilsAbstract::getValueFromCurrency($myPrice);
		//initialize values
		$finalOutputArray = array(
				'sku'             => $sku
				,'myPrice'        => $myPrice
				,'minPrice'       => 0
				,'companyPrices'  => array()
		);
		foreach($companyAliases as $key => $value)
		{
			$finalOutputArray['companyPrices'][$key] = array('price' => 0, 'priceURL' => '', 'PriceMatchCompanyId' => $value['PriceMatchCompanyId']);
		}
		$url = 'http://www.staticice.com.au/cgi-bin/search.cgi';
		//getting actual values
		$productPriceArray = HTMLParser::getPriceListForProduct($url, $sku);
		foreach($productPriceArray as $productPriceInfo)
		{
			if(($companyDetails = trim($productPriceInfo['companyDetails'])) === '')
				continue;
		
			$cdArray = explode('|', $companyDetails);
			$companyURL = (isset($cdArray[count($cdArray) - 2])) ? trim($cdArray[count($cdArray) - 2]) : trim($companyDetails);
		
			foreach($companyAliases as $key => $value)
			{
				if(is_array($value) === true && in_array(strtolower($companyURL), array_map(create_function('$a', 'return strtolower($a);'), $value)))
				{
					$price = str_replace(' ', '', str_replace('$', '', str_replace(',', '', $productPriceInfo['price']) ) );
					if($finalOutputArray['minPrice'] == 0 || $finalOutputArray['minPrice'] > $price)
						$finalOutputArray['minPrice'] = $price;
					$finalOutputArray['companyPrices'][$key] = array(
							'price' => $price
							,'priceURL' => HTMLParser::getHostUrl($url) . $productPriceInfo['priceLink']
							,'PriceMatchCompanyId' => $value['PriceMatchCompanyId'] // added b/c improvment of pricematch (Jun2015). it keeps PriceMatchCompany for further reference
					);
					break;
				}
			}
		}
		$companyAliases = null;
		$price = null;
		$productPriceArray = null;
			
		//return the result
		$finalOutputArray['priceDiff'] = ($finalOutputArray['myPrice'] - $finalOutputArray['minPrice']);
		return $finalOutputArray;
	}
}