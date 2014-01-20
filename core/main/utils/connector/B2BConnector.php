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
	/**
	 * Import Orders
	 * 
	 * @param string $lastUpdatedTime The datatime string
	 * 
	 * @return B2BConnector
	 */
	public function importOrders($lastUpdatedTime = '')
	{
		$transStarted = false;
		try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
		
		try 
		{
			if(($lastUpdatedTime = trim($lastUpdatedTime)) === '')
			{
				$lastImportTime = new UDate(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME));
				$lastImportTime->setTimeZone(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_TIMEZONE));
				$lastUpdatedTime = trim($lastImportTime);
			}
			
			$now = new UDate();
			$orders = $this->getlastestOrders($lastUpdatedTime);
			foreach($orders as $order)
			{
				if(($status = trim($order->state)) === '')
					continue;
				$totalDue = (!isset($order->total_due) ? 0 : trim($order->total_due));
				$o = new Order();
				$orderDate = new UDate(trim($order->created_at), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_TIMEZONE));
				$orderDate->setTimeZone('UTC');
				
				$o->setOrderNo(trim($order->increment_id))
				  ->setOrderDate(trim($orderDate))
				  ->setTotalAmount(trim($order->grand_total))
				  ->setStatus(OrderStatus::createStatus($status))
				  ->setTotalPaid(trim($order->grand_total)*1 - $totalDue*1);
				FactoryAbastract::dao('Order')->save($o);
				
				OrderInfo::create($o, OrderInfoType::get(OrderInfoType::ID_CUS_NAME), trim($order->customer_firstname) . ' ' . trim($order->customer_lastname));
				OrderInfo::create($o, OrderInfoType::get(OrderInfoType::ID_CUS_EMAIL), trim($order->customer_email));
				OrderInfo::create($o, OrderInfoType::get(OrderInfoType::ID_CUS_BILL_ADDR), trim($order->customer_email));
				OrderInfo::create($o, OrderInfoType::get(OrderInfoType::ID_CUS_SHIP_ADDR), trim($order->customer_email));
				OrderInfo::create($o, OrderInfoType::get(OrderInfoType::ID_CUS_SHIP_PC), trim($order->customer_email));
			}
			SystemSettings::addSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME, trim($now));
			
			if($transStarted === false)
				Dao::commitTransaction();
		}
		catch(Exception $e)
		{
			if($transStarted === false)
				Dao::rollbackTransaction();
			throw $e;
		}
		return $this;
	}
	/**
	 * Getting the list of lastest updated orders
	 * 
	 * @param string $lastUpdatedTime The datatime string
	 * 
	 * @return array
	 */
	public function getlastestOrders($lastUpdatedTime)
	{
		$params = array(
			'complex_filter' => array(
				array(
						'key' => 'updated_at',
						'value' => array(
								'key' => 'gt',
								'value' => $lastUpdatedTime
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