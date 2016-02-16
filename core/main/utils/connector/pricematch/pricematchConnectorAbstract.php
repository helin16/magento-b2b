<?php
abstract class pricematchConnectorAbstract
{
	const BASE_URL = '';
	const URL_PREFIX = '';
	const HTML_DOM_OBJECT_NAME = 'simple_html_dom';
	const HTML_DOM_NODE_OBJECT_NAME = 'simple_html_dom_node';
	const APC_TTL = 3600 ; //apc is having an hour life time
	const USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
	const CURL_TIMEOUT = 60; //seconds
	const CURL_CUSTOM_REQUEST = 'POST';
	protected static $search_panel_indicators = array();
	protected static $dom_selectors = array();
	protected $debug = true;
	protected static $_cache;

	
	protected static function getUrlHeader()
	{
		$class = get_called_class();
		$result =  ( trim($class::BASE_URL) . trim($class::URL_PREFIX) );
		if($result === '')
			throw new Exception('empty url header found');
		return $result;
	}
	
	public static function readUrl($url, $timeout = null, array $data = array(), $customerRequest = '', $extraOpts = array(), $debug = false)
	{
		if (! isset ( $extraOpts [CURLOPT_USERAGENT] ))
			$extraOpts [CURLOPT_USERAGENT] = self::USER_AGENT;
		$data = ComScriptCURL::readUrl ( $url, $timeout, $data, $customerRequest, $extraOpts , $debug);
		$dom = new simple_html_dom ();
		$dom->load ( $data );
		return $dom;
	}
	protected static function isSearchPanel($txt)
	{
		$class = get_called_class();
		if(!isset($class::$search_panel_indicators) || !is_array($class::$search_panel_indicators))
			return false;
		foreach ($class::$search_panel_indicators as $indicator)
		{
			if(trim($indicator) === '')
				continue;
			if(strpos($txt, $indicator) !== false)
				return true;
		}
		return false;
	}
	public static function find($parent, $selector) 
	{
		if(get_class($parent) !== self::HTML_DOM_NODE_OBJECT_NAME || trim($selector) === '')
			return false;
		return count($tmp = $parent->find($selector)) > 0 ? $tmp[0] : false;
	}
	public static function fillBaseUrl($url) {
		$class = get_called_class();
		if(strlen($url) === 0 || strpos($url, $class::BASE_URL) !== false)
			return $url;
		$url = trim($url);
		if(substr($url, 0, 1) === '/' || substr($url, 0, 1) === "\\")
			$url = substr($url, 1);
		$url = $class::BASE_URL . $url;
		return $url;
	}
	public static function getUrlDestination($url)
	{
		$url = trim($url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$a = curl_exec($ch); // $a will contain all headers
		
		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
		
		return $url;
	}
}