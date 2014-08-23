<?php
/**
 * The abstract class
 * 
 * @author mrahman
 *
 */
abstract class HTMLParser
{
	const HTML_DOM_OBJECT_NAME = 'simple_html_dom';
	
	private static $_cache;
	const APC_TTL = 3600 ; //apc is having an hour life time
	
	public static function getWebsite($url)
	{
		$key = md5($url);
		if(!isset(self::$_cache[$key]))
		{
			//try to use apc
	    	if(extension_loaded('apc') && ini_get('apc.enabled'))
	    	{
	    		if(!apc_exists($key))
	    			apc_add($key, self::_readUrl($url), self::APC_TTL);
    			self::$_cache[$key] = apc_fetch($key);
	    	}
	    	else
	    		self::$_cache[$key] = self::_readUrl($url);
		}
		return self::$_cache[$key];
	}
	
	private static function _readUrl($url)
	{
		$data = ComScriptCURL::readUrl($url);
		$dom = new simple_html_dom();
		$dom->load($data); 
		return $dom;
	}
	
	public static function getHostUrl($url)
	{
		$parts = parse_url($url);
		return trim($parts['scheme']) . '://' . trim($parts['host']);
	}

	public static function getPriceListForProduct($url, $productName)
	{
		 if(($productName = trim($productName)) === '')
		 	throw new Exception("Product name must be provided to get the price list");
		 
		 $outputArray = array();
		 
		 try 
		 {
			$array = array(
		 		'start' => 1,
		 		'links' => PHP_INT_MAX,
				'q'=> $productName
			);
			$url = $url . '?' . http_build_query($array);
			$data = self::getWebsite($url);
			foreach($data->find("tr td a[target]") as $index => $l)
			{	
				if(preg_match('/^\$\d+.*\d+$/', $l->plaintext))
				{
					$tmp = array();
					$tmp['price'] = trim($l->plaintext);
					$tmp['priceLink'] = trim($l->href);
					$tmp['companyDetails'] = $l->parent()->next_sibling()->plaintext;
					
					if($l->parent()->next_sibling()->find("font a[target]") > 0)
					{
						foreach($l->parent()->next_sibling()->find("font a[target]") as $companyLink)
						{
							$tmp['companyName'] = trim($companyLink->plaintext);
							$tmp['companyLink'] = $companyLink->href;
						}
					}
					$outputArray[] = $tmp;
				}
			}
		 }
		 catch(Exception $ex)
		 {
		 	throw new Exception('Unexpected exception occured:['.$ex->getMessage().']');
		 }
		 
		return $outputArray;
	}
}

