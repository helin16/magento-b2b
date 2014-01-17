<?php
class B2BConnector
{
	private $_apiUser;
	private $_apiKey;
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
	private $_session;
	
	public function __construct($wsdl, $apiUser, $apiKey)
	{
		$this->_soapClient = new SoapClient($wsdl, array('exceptions' => true, 'encoding'=>'utf-8', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP));
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
	private function _connect()
	{
		if(($this->_session = trim($this->_session)) === '')
			$this->_session = $this->_soapClient->login($this->_apiUser, $this->_apikey);
		return $this->_soapClient;
	}
	public function getlastestOrders($lastUpdatedTime)
	{
		$params = array(
			'complex_filter' => array(
				array(
						'key' => 'created_at',
						'value' => array(
								'key' => 'gt',
								'value' => '2014-01-01 12:12:07'
						),
				),
			)
		);
		return $this->_connect()->salesOrderList($this->_session, $params);
	}
	
	public function getOrderInfo($orderId)
	{
		return $this->_connect()->salesOrderInfo($this->_session, $orderId);
	}
}