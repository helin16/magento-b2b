<?php
class ComScriptSoap
{
	/**
	 * @SoapClient
	 */
	private $_client;
	/**
	 * The cache of script objects
	 * @var unknown
	 */
	private static $_cache;
	/**
	 * The exception object from last soapcall
	 * 
	 * @var Exception|null
	 */
	private $_callError = false;
	/**
	 * Getting the BmvComScriptSoap
	 *  
	 * @param string $wsdl
	 * @param string $options
	 * 
	 * @return BmvComScriptSoap
	 */
	public static function getScript($wsdl, $options = null)
	{
		$key = md5($wsdl . json_encode($options));
		if(!isset(self::$_cache[$key]))
		{
			$className = trim(get_called_class());
			self::$_cache[$key] = new $className($wsdl, $options);
		}
		return self::$_cache[$key];
	}
	/**
	 * constructor
	 * 
	 * @param unknown $wsdl
	 * @param string $params
	 */
	public function __construct($wsdl, $options = null)
	{
		if($options === null)
			$options = array('exceptions' => true, 'encoding'=>'utf-8', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP);
// 		$options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal",'proxy_port' => 3128));
		$this->_client = new SoapClient($wsdl, $options);
	}
	/**
	 * Returns the last exception object from last soap call
	 * 
	 * @return Exception|null
	 */
	public function getLastCallError()
	{
		return $this->_callError;
	}
	/**
	 * Calling the function of a soup
	 * 
	 * @param string $funcName
	 * @param string $params
	 * 
	 * @return SimpleXMLElement|null
	 */
	public function __call($funcName, $params)
	{
		$result = null;
		$this->_callError = null;
		try 
		{
			$result = $this->_client->__soapCall($funcName, $params);
			$result = new SimpleXMLElement($result);
		}
		catch (Exception $ex)
		{
			$this->_callError = $ex;
		}
		return $result;
	}
}