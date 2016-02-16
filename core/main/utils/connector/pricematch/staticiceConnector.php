<?php
class staticiceConnector extends pricematchConnectorAbstract {
	const BASE_URL = 'http://staticice.com.au/';
	const URL_PREFIX = 'cgi-bin/search.cgi';
	protected static $dom_selectors= array (
			'row' => 'table tbody tr[valign="top"]',
			'price' => 'td[align="left"] a',
			'description' => 'td[valign="bottom"]',
			'subDescription' => 'font',
			'vendorLink' => 'a[href]',
			'vendorImage' => 'img',
			'vendorName' => 'b font',
			'subDescription' => 'font' 
	);
	
	public static function getPrices($sku, &$cache = null, $debug = true) 
	{
		self::$_cache = $cache;

		
		if (($productName = trim( $sku)) === '')
			return;

		$array = array (
				'start' => 1,
				'links' => PHP_INT_MAX,
				'showadres' => 1,
				'q' => $productName 
		);
		$extraOpts =  array();		
		$rowResults = array();
		$rowResults['items'] = array();
		$data = self::readUrl ( self::getUrlHeader (), self::CURL_TIMEOUT, $array, self::CURL_CUSTOM_REQUEST , $extraOpts , $debug);
		foreach ( $data->find (self::$dom_selectors['row']) as $tr ) {
			try {
				if (($text = trim ( htmlspecialchars_decode ( $tr->plaintext ) )) === '' || self::isSearchPanel ( $text ) === true)
					continue;
				$price = 0;
				$description = $productLink = $companyLink = $img = $companyName = $companyBaseUrl = $companyLocation = $updated = '';
				
				$priceEl = self::find($tr, self::$dom_selectors['price']);
				$productLink = $priceEl->href;
				$productLink = (trim($productLink) === '' ? '' : self::fillBaseUrl($productLink));
				
				$price = StringUtilsAbstract::getValueFromCurrency($priceEl->plaintext);
				if(($price = doubleval($price)) <= doubleval(0))
					continue;
				
				$descriptionEl = self::find($tr, self::$dom_selectors['description']);
				
				if(get_class($descriptionEl) === self::HTML_DOM_NODE_OBJECT_NAME)
				{
					$description = trim($descriptionEl->plaintext);
					$subDescriptionEl = self::find($descriptionEl, self::$dom_selectors['subDescription']);
					if(is_object($subDescriptionEl) && get_class($subDescriptionEl) === self::HTML_DOM_NODE_OBJECT_NAME)
					{
						$subDescription = $subDescriptionEl->plaintext;
						$description = str_replace($subDescription, '', $description);
						$description = str_replace(PHP_EOL, '', $description);
						
						$linkEl = self::find($subDescriptionEl, self::$dom_selectors['vendorLink']);
						if(is_object($linkEl) && get_class($linkEl) === self::HTML_DOM_NODE_OBJECT_NAME)
						{
							$img = (count($imgEl = $linkEl->find('img')) > 0 ? ($imgEl[0]->src) : '');
							$img = ((is_object($imgEl = self::find($linkEl, self::$dom_selectors['vendorImage'])) && get_class($imgEl) === self::HTML_DOM_NODE_OBJECT_NAME) ? trim($imgEl->src) : '');
							$img = self::fillBaseUrl($img);
							
							$companyName = ((is_object($companyNameEl = self::find($linkEl, self::$dom_selectors['vendorName'])) && get_class($companyNameEl) === self::HTML_DOM_NODE_OBJECT_NAME) ? ($companyNameEl->plaintext) : '');
							$companyLink = self::fillBaseUrl($linkEl->href);
						}
						$subDescriptionArray = explode(' | ', $subDescription);
						if(count($subDescriptionArray) === 3)
						{
							foreach ($subDescriptionArray as $index => $string)
							{
								$string = trim($string);
								switch($index)
								{
									case 0:
										{
											if(trim($companyName) === '')
												$companyName = trim(preg_replace('/\s*\([^)]*\)/', '', $string));
											$string = trim(str_replace($companyName, '', $string));
											preg_match('/\(([^\)]*)\)/', $string, $match);
											if(count($match) > 1)
												$companyLocation = $match[1];
											break;
										}
									case 1:
										{
											$string = 'http://' . $string;
											if (!filter_var($string, FILTER_VALIDATE_URL) === false)
												$companyBaseUrl = $string;
											break;
										}
									case 2:
										{
											if(strpos($string, 'updated:') !== false && ($tmp = trim(str_replace('updated:', '', $string))) && ($tmp = new UDate($tmp)) instanceof UDate)
												$updated = $tmp;
											break;
										}
								}
							}
						}
					}
				}
					
				$rowResult = array(
						'price' => $price,
						'url' => (trim($productLink) === '' ? '' : self::getUrlDestination($productLink)),
						//'description' => $description,
						'name' => $companyName,
						//'company location' => $companyLocation,
						'updated' => $updated
				);
				$rowResults['items'][] = $rowResult;
				
			} catch (Exception $ex) {
				echo '***warning***' . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString() . PHP_EOL;
				continue;
			}
		}
		unset($data);
		return $rowResults;
	}
	public static function getUrlDestination($url)
	{
		$url = trim($url);
		
		$components = parse_url($url);
		if(isset($components['query']))
		{
			parse_str($components['query'], $query);
			if(isset($query['newurl']) && ($newUrl = trim($query['newurl'])) !== '')
				return $newUrl;
		}
		return parent::getUrlDestination($url);
	}
}