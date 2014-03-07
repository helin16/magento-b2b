<?php
class OrderConnector extends B2BConnector
{
	/**
	 * Import Orders
	 *
	 * @param string $lastUpdatedTime The datatime string
	 *
	 * @return B2BConnector
	 */
	public function importOrders($lastUpdatedTime = '')
	{
		$totalItems = 0;
		Log::logging(0, get_class($this), 'starting ...', self::LOG_TYPE, 'start', __FUNCTION__);
		if(($lastUpdatedTime = trim($lastUpdatedTime)) === '')
		{
			Log::logging(0, get_class($this), 'Getting the last updated time', self::LOG_TYPE, '$lastUpdatedTime is blank', __FUNCTION__);
// 			$lastImportTime = new UDate(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_TIMEZONE));
			$lastUpdatedTime = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME));
		}
		
		//getting the lastest order since last updated time
		$orders = $this->getlastestOrders($lastUpdatedTime);
		Log::logging(0, get_class($this), 'Found ' . count($orders) . ' order(s) since "' . $lastUpdatedTime . '".', self::LOG_TYPE, '', __FUNCTION__);
		foreach($orders as $index => $order)
		{
			$transStarted = false;
			try
			{
				try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
				
				$order = $this->getOrderInfo(trim($order->increment_id));
				Log::logging(0, get_class($this), 'Found order from Magento with orderNo = ' . trim($order->increment_id) . '.', self::LOG_TYPE, '', __FUNCTION__);
				if(($status = trim($order->state)) === '')
				{
					Log::logging(0, get_class($this), 'Found no state Elment from $order, next element!', self::LOG_TYPE, '$index = ' . $index, __FUNCTION__);
					continue;
				}
	
				//saving the order
				$orderDate = new UDate(trim($order->created_at), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_TIMEZONE));
				$orderDate->setTimeZone('UTC');
				$totalPaid = (!isset($order->total_paid) ? 0 : trim($order->total_paid));
	
				$shippingAddr = $billingAddr = null;
				if(!($o = Order::get(trim($order->increment_id))) instanceof Order)
				{
					$o = new Order();
					Log::logging(0, get_class($this), 'Found no order from DB, create new', self::LOG_TYPE, '$index = ' . $index, __FUNCTION__);
				}
				else
				{
					$shippingAddr = $o->getShippingAddr();
					$billingAddr = $o->getBillingAddr();
					Log::logging(0, get_class($this), 'Found order from DB, ID = ' . $o->getId(), self::LOG_TYPE, '$index = ' . $index, __FUNCTION__);
				}
	
				$o->setOrderNo(trim($order->increment_id))
					->setOrderDate(trim($orderDate))
					->setTotalAmount(trim($order->grand_total))
					->setStatus((strtolower($status) === 'canceled' ? OrderStatus::get(OrderStatus::ID_CANCELLED) : OrderStatus::get(OrderStatus::ID_NEW)))
					->setTotalPaid($totalPaid)
					->setIsFromB2B(true)
					->setShippingAddr($this->_createAddr($order->billing_address, $shippingAddr))
					->setBillingAddr($this->_createAddr($order->shipping_address, $billingAddr));
				FactoryAbastract::dao('Order')->save($o);
				Log::logging(0, get_class($this), 'Saved the order, ID = ' . $o->getId(), self::LOG_TYPE, '$index = ' . $index, __FUNCTION__);
	
				//create order info
				$this->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_CUS_NAME), isset($order->customer_firstname) ? trim($order->customer_firstname) . ' ' . trim($order->customer_lastname) : '')
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_CUS_EMAIL), trim($order->customer_email))
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_QTY_ORDERED), intval(trim($order->total_qty_ordered)))
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_STATUS), trim($order->status))
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_STATE), trim($order->state))
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_TOTAL_AMOUNT), trim($order->grand_total))
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_PAID_AMOUNT), $totalPaid)
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD), trim($order->shipping_description))
					->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_PAYMENT_METHOD), (!isset($order->payment) ? '' : (!isset($order->payment->method) ? '' : trim($order->payment->method))));
				Log::logging(0, get_class($this), 'Updated order info', self::LOG_TYPE, '$index = ' . $index, __FUNCTION__);
	
				//saving the order item
				foreach($order->items as $item)
					$this->_createItem($o, $item);
				
				$totalItems++;
				
				//record the last imported time for this import process
				SystemSettings::addSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME, trim($order->created_at));
				Log::logging(0, get_class($this), 'Updating the last updated time', self::LOG_TYPE, '', __FUNCTION__);
				
				if($transStarted === false)
					Dao::commitTransaction();
			}
			catch(Exception $e)
			{
				if($transStarted === false)
					Dao::rollbackTransaction();
				throw $e;
			}
		}
				
		return $lastUpdatedTime . " => " . SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME) . ' => ' . $totalItems;
// 		return $this;
	}
	/**
	 * creating order info
	 *
	 * @param Order         $order
	 * @param OrderInfoType $type
	 * @param string        $value
	 *
	 * @return B2BConnector
	 */
	private function _createOrderInfo(Order $order, OrderInfoType $type, $value)
	{
		$items = OrderInfo::find($order, $type);
		if(count(OrderInfo::find($order, $type)) > 0 )
		{
			foreach($items as $item)
				OrderInfo::create($order, $type, $value, $item);
		}
		else
		{
			OrderInfo::create($order, $type, $value);
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
		return Address::create(isset($addressObj->street) ? trim($addressObj->street) : '',
				trim(isset($addressObj->city) ? trim($addressObj->city) : ''),
				isset($addressObj->region) ? trim($addressObj->region) : '',
				trim(isset($addressObj->country_id) ? trim($addressObj->country_id) : ''),
				trim(isset($addressObj->postcode) ? trim($addressObj->postcode) : ''),
				trim(trim(isset($addressObj->firstname) ? trim($addressObj->firstname) : '') . ' ' . trim(isset($addressObj->lastname) ? trim($addressObj->lastname) : '')),
				trim(isset($addressObj->telephone) ? trim($addressObj->telephone) : ''),
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
				Product::create(trim($itemObj->sku), trim($itemObj->name), trim($itemObj->product_id)),
				trim($itemObj->price),
				trim($itemObj->qty_ordered),
				trim($itemObj->row_total),
				trim($itemObj->item_id)
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
								'key' => 'created_at',
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
	/**
	 * change the order status to something in Magento
	 * 
	 * @param Order  $order         The order
	 * @param string $orderStatus   The new status of the order
	 * @param string $comments      The new comments fo the order
	 * @param bool   $notifCustomer Whether we want to notify the customer
	 * 
	 * @return bool Whether the action has done successfully
	 */
	public function changeOrderStatus(Order $order, $orderStatus, $comments = '', $notifCustomer = false)
	{
		return $this->_connect()->salesOrderAddComment($this->_session, $order->getOrderNo(), $orderStatus, $comments, $notifCustomer);
	}
	/**
	 * Adding comments to a order in Magento
	 * 
	 * @param Order  $order         The order
	 * @param string $comments      The new comments fo the order
	 * @param bool   $notifCustomer Whether we want to notify the customer
	 */
	public function addComments(Order $order, $comments = '', $notifCustomer = false)
	{
		return $this->_connect()->salesOrderAddComment($this->_session, $order->getOrderNo(), $order->getInfo(OrderInfoType::ID_MAGE_ORDER_STATUS), $comments, $notifCustomer);
	}
	/**
	 * Hold an order
	 * 
	 * @param Order  $order         The order
	 * 
	 * @return bool Whether the action has done successfully
	 */
	public function holdOrder(Order $order)
	{
		return $this->_connect()->salesOrderHold($this->_session, $order->getOrderNo());
	}
	/**
	 * unHold an order
	 * 
	 * @param Order  $order         The order
	 * 
	 * @return bool Whether the action has done successfully
	 */
	public function unHoldOrder(Order $order)
	{
		return $this->_connect()->salesOrderUnhold($this->_session, $order->getOrderNo());
	}
	/**
	 * Cancel an order
	 * 
	 * @param Order  $order         The order
	 * 
	 * @return bool Whether the action has done successfully
	 */
	public function cancelOrder(Order $order)
	{
		return $this->_connect()->salesOrderCancel($this->_session, $order->getOrderNo());
	}
}
