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
			//record the last imported time for this import process
			SystemSettings::addSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME, trim(new UDate()));
			
			//getting the lastest order since last updated time
			$orders = $this->getlastestOrders($lastUpdatedTime);
			foreach($orders as $order)
			{
				$order = $this->getOrderInfo(trim($order->increment_id));
				var_dump($order);
				if(($status = trim($order->state)) === '')
					continue;
				//saving the order
				$orderDate = new UDate(trim($order->created_at), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_TIMEZONE));
				$orderDate->setTimeZone('UTC');
				$totalPaid = (!isset($order->total_paid) ? 0 : trim($order->total_paid));
				
				$shippingAddr = $billingAddr = null;
				if(!($o = Order::get(trim($order->increment_id))) instanceof Order)
					$o = new Order();
				else
				{
					$shippingAddr = $o->getShippingAddr();
					$billingAddr = $o->getBillingAddr();
				}
				$o->setOrderNo(trim($order->increment_id))
				  ->setOrderDate(trim($orderDate))
				  ->setTotalAmount(trim($order->grand_total))
				  ->setStatus(OrderStatus::createStatus($status))
				  ->setTotalPaid($totalPaid)
				  ->setShippingAddr($this->_createAddr($order->billing_address, $shippingAddr))
				  ->setBillingAddr($this->_createAddr($order->shipping_address, $billingAddr));
				FactoryAbastract::dao('Order')->save($o);
				
				OrderInfo::create($o, OrderInfoType::get(OrderInfoType::ID_CUS_NAME), trim($order->customer_firstname) . ' ' . trim($order->customer_lastname));
				OrderInfo::create($o, OrderInfoType::get(OrderInfoType::ID_CUS_EMAIL), trim($order->customer_email));
				
				//saving the order item
				foreach($order->items as $item)
					$this->_createItem($o, $item);
			}
			
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
	 * Creating an address ojbect from Magento
	 * 
	 * @param stdClass $addressObj The stdclass of the address object
	 * 
	 * @return Address
	 */
	private function _createAddr($addressObj, &$exsitAddr = null)
	{
		return Address::create(trim($addressObj->street), 
				trim($addressObj->city), 
				isset($addressObj->region) ? trim($addressObj->region) : '', 
				trim($addressObj->country_id), 
				trim($addressObj->postcode), 
				trim($addressObj->firstname) . ' ' . trim($addressObj->lastname), 
				trim($addressObj->telephone),
				$exsitAddr
		);
	}
	/**
	 * Creating the order item for an order
	 * 
	 * @param Order    $order
	 * @param stdClass $itemObj
	 * 
	 * @return Ambigous <Ambigous, OrderItem, BaseEntityAbstract>
	 */
	private function _createItem(Order $order, $itemObj)
	{
		return OrderItem::create($order, 
				Product::create(trim($itemObj->sku), trim($itemObj->name)),
				trim($itemObj->price),
				trim($itemObj->qty_ordered),
				trim($itemObj->row_total)
		);
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
	
	/**
	 * Getting the information of an order
	 * 
	 * @param string $orderId The id of the order
	 * 
	 * @return array
	 */
	public function getOrderInfo($orderId)
	{
		return $this->_connect()->salesOrderInfo($this->_session, $orderId);
	}
}