<?php
class PriceMatchConnector
{
	private $sku;
	private $base_url = 'http://www.staticice.com.au/cgi-bin/search.cgi';
	private $debug;
	/**
	 * runner for PriceMatchConnector
	 *
	 * @param Product $product
	 * @param array $companies
	 * @return PriceMatchMin
	 * @throws Exception
	 */
	public static function run($sku, $debug = false)
	{
		$class = new self();
		$class->sku = trim($sku);
		$class->debug = $debug === true ? true : false;
		
		$class->recordResult($class->getPrices());
	}
	/**
	 * put given price match result for all companies into PriceMatchRecord table
	 *
	 * @param unknown $companyPrices
	 * @return PriceMatchConnector
	 */
	private function recordResult($priceMatchResults)
	{
		foreach ($priceMatchResults as $priceMatchResult)
		{
			
			$company = $priceMatchResult['PriceMatchCompany'];
			$price = doubleval($priceMatchResult['price']);
			$url = $priceMatchResult['url'];
			$name = $priceMatchResult['name'];
			
			$min = PriceMatchMin::create($this->sku); // to create PriceMatchRecord must have a PriceMatchMin, the record for PriceMatchMin will be null at this time instance
			
			if(abs($price) !== doubleval(0) && $price > doubleval(0) && trim($price) !== '') // price must be positive (non-zero), otherwise will be rejected by Core::PriceMatchRecord::create()
			{
				PriceMatchRecord::create($company, $min, $price, $url);
			}
		}
		return $this;
	}
	/**
	 * Getting the price match result
	 * 
	 * @return array
	 */
	private function getPrices()
	{
		$result = array();
		$priceMatchResults = HTMLParser::getPriceListForProduct($this->base_url, $this->sku);
		
		foreach($priceMatchResults as $priceMatchResult)
		{
			if(($companyDetails = trim($priceMatchResult['companyDetails'])) === '')
				continue;
		
			$companyDetailsArray = explode('|', $companyDetails);
			$companyURL = (isset($companyDetailsArray[count($companyDetailsArray) - 2])) ? trim($companyDetailsArray[count($companyDetailsArray) - 2]) : trim($companyDetails);
			$companyURL = strtolower($companyURL);
			$companyURL = str_replace('https://', '', $companyURL);
			$companyURL = str_replace('http://', '', $companyURL);
			$name = (isset($companyDetailsArray[count($companyDetailsArray) - 3])) ? trim($companyDetailsArray[count($companyDetailsArray) - 3]) : trim($companyDetails);
			$price = str_replace(' ', '', str_replace('$', '', str_replace(',', '', $priceMatchResult['price']) ) );
			$url = HTMLParser::getHostUrl($this->base_url) . $priceMatchResult['priceLink'];
			
			foreach (PriceMatchCompany::getAll() as $company)
			{
				if($companyURL === strtolower($company->getCompanyAlias()))
				{
					$result[] = array('PriceMatchCompany'=> $company, 'price'=> $price, 'name'=> $name, 'url'=> $url);
					if($this->debug === true)
						echo $company->getCompanyName() . '(id=' . $company->getId() . "), $" . $price . ", " . $name . ", " . $url . "\n";
				}
			}
		}
		return $result;
	}
}
