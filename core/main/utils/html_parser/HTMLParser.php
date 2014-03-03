<?php
/**
 * The abstract class
 * 
 * @author mrahman
 *
 */
abstract class HTMLParser
{
	const URL = 'http://www.staticice.com.au/cgi-bin/search.cgi?q=';
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
	    	{
	    		self::$_cache[$key] = self::_readUrl($url);
	    	}
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

	public static function getPriceListForProduct($productName)
	{
		 if(($productName = trim($productName)) === '')
		 	throw new Exception("Product name must be provided to get the price list");
		 
		 try 
		 {
			$url = self::URL.str_replace("'", '%27', str_replace(' ', '+', str_replace('&', '%26', str_replace('+', '%2B', $productName))));
			var_dump($url);
			$data = self::getWebsite($url);
			var_dump($data->nodes); die();
		 }
		 catch(Exception $ex)
		 {
		 	throw new Exception('Unexpected exception occured:['.$ex->getMessage().']');
		 }
		  
	}
}

