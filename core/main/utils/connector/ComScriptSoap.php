<?php
class ComScriptSoap extends SoapClient
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
}