<?php
abstract class B2BConnector
{
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
	 * The WSDL for the soapclient
	 *
	 * @var string
	 */
	private $_wsdl;
	/**
	 * The user for the soapclient
	 *
	 * @var string
	 */
	private $_apiUser;
	/**
	 * The key for the soapclient
	 *
	 * @var string
	 */
	private $_apiKey;
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
		$this->_wsdl = trim($wsdl);
		$this->_apiUser = trim($apiUser);
		$this->_apiKey = trim($apiKey);
		$options = array('exceptions' => true, 'encoding'=>'utf-8', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP);
// 		$options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal", 'proxy_port' => 3128));
		$this->_soapClient = ComScriptSoap::getScript($wsdl, $options);
	}
	/**
	 * Getting the wsdl
	 *
	 * @return string
	 */
	protected function _getWSDL()
	{
		return trim($this->_wsdl);
	}
	/**
	 * Getting the api user
	 *
	 * @return string
	 */
	protected function _getApiUser()
	{
		return trim($this->_apiUser);
	}
	/**
	 * Getting the api key
	 *
	 * @return string
	 */
	protected function _getApiKey()
	{
		return trim($this->_apiKey);
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
			$this->_session = $this->_soapClient->login($this->_apiUser, $this->_apiKey);
		return $this->_soapClient;
	}
	/**
	 * loging the debug output
	 *
	 * @param unknown $entityId
	 * @param unknown $entityName
	 * @param unknown $msg
	 * @param unknown $type
	 * @param string $comments
	 * @param string $funcName
	 * @return B2BConnector
	 */
	protected function _log($entityId, $entityName, $msg, $type, $comments = '', $funcName = '')
	{
		//echo $msg . "\n";
		Log::logging($entityId, $entityName, $msg, $type, $comments, $funcName);
		return $this;
	}
}