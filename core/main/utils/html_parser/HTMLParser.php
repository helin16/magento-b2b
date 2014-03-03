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
	
	public static function getWebsite($url)
	{
		
	}
	
	private static function _readUrl($url)
	{
		$curl = curl_init();
		$options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_PROXY => 'proxy.bytecraft.internal:3128'
		);
		curl_setopt_array($curl, $options);
		$data = curl_exec($curl);
		curl_close($curl);
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
			$data = self::_readUrl($url);
			var_dump($data); die();
		 }
		 catch(Exception $ex)
		 {
		 	throw new Exception('Unexpected exception occured:['.$ex->getMessage().']');
		 }
		  
	}
}

