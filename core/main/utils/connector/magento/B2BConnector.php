<?php
abstract class B2BConnector
{
	private $_apiUser;
	private $_apiKey;
	
	const LOG_TYPE = 'Connector';
	const CONNECTOR_TYPE_ORDER = 'Order';
	const CONNECTOR_TYPE_SHIP = 'Shippment';
	const CONNECTOR_TYPE_CATELOG = 'Catelog';
	const CONNECTOR_TYPE_CUSTOMER = 'Customer';
	/**
	 * SoapClient
	 * 
	 * @var SoapClient
	 */
	private $_soapClient;
	/**
	 * The session string
	 * 
	 * @var string
	 */
	protected $_session;
	/**
	 * The cache for all the connector objects
	 * 
	 * @var array
	 */
	public static $_cache;
	/**
	 * Getting a B2BConnector
	 * 
	 * @param string $type    The type of the connector
	 * @param string $wsdl    The wsdl for the webservice
	 * @param string $apiUser The username for the webservice
	 * @param string $apiKey  The password for the webservice
	 * 
	 * @return B2BConnector
	 */
	public static function getConnector($type, $wsdl, $apiUser, $apiKey)
	{
		$key = md5($type . $wsdl . $apiUser . $apiKey);
		if(!isset(self::$_cache[$key]))
		{
			$className = $type . 'Connector';
			self::$_cache[$key] = new $className($wsdl, $apiUser, $apiKey);
			if(!self::$_cache[$key] instanceof B2BConnector)
				throw new ConnectorException($className . ' is not a ' . get_called_class() . '!');
		}
		return self::$_cache[$key];
	}
	/**
	 * constructor
	 * 
	 * @param unknown $wsdl
	 * @param unknown $apiUser
	 * @param unknown $apiKey
	 */
	public function __construct($wsdl, $apiUser, $apiKey)
	{
		$options = array('exceptions' => true, 'encoding'=>'utf-8', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP);
// 		$options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal", 'proxy_port' => 3128));
		$this->_soapClient = ComScriptSoap::getScript($wsdl, $options);
		$this->_apiUser = $apiUser;
		$this->_apikey = $apiKey;
	}
	/**
	 * start the session for soap
	 * 
	 * @param string $apiUser
	 * @param string $apiKey
	 * 
	 * @return SoapClient
	 */
	protected function _connect()
	{
		if(($this->_session = trim($this->_session)) === '')
			$this->_session = $this->_soapClient->login($this->_apiUser, $this->_apikey);
		return $this->_soapClient;
	}
}