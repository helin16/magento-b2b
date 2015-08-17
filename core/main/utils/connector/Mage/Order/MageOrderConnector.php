<?php
class MageOrderConnector extends MageConnectorAbstract
{
	/**
	 * Import Orders
	 *
	 * @param string $lastUpdatedTime The datatime string
	 *
	 * @return B2BConnector
	 */
	public static function importOrders($lastUpdatedTime = '')
	{
		$totalItems = 0;
		self::_log('starting ...', self::LOG_TYPE, 0, get_class($this), 'start', __FUNCTION__);
		if(($lastUpdatedTime = trim($lastUpdatedTime)) === '') {
			self::_log('Getting the last updated time', self::LOG_TYPE, 0, get_class($this), '$lastUpdatedTime is blank', __FUNCTION__);
			$lastUpdatedTime = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME));
		}

		//getting the lastest order since last updated time
		$orders = self::getLastestOrders($lastUpdatedTime);
		self::_log('Found ' . count($orders) . ' order(s) since "' . $lastUpdatedTime . '".', self::LOG_TYPE, 0, get_class($this), '', __FUNCTION__);
		if(is_array($orders) && count($orders) > 0) {
			$transStarted = false;
			try {
				try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}

				foreach($orders as $index => $order) {
					self::_log('Found order from Magento with orderNo = ' . trim($order->increment_id) . '.', self::LOG_TYPE, 0, get_class($this), '', __FUNCTION__);

					$order = $this->getOrderInfo(trim($order->increment_id));
					if(!is_object($order)) {
						self::_log('Found no object from $order, next element!', self::LOG_TYPE, 0, get_class($this), '$index = ' . $index, __FUNCTION__);
						continue;
					}
					if(($status = trim($order->state)) === '') {
						self::_log('Found no state Elment from $order, next element!', self::LOG_TYPE, 0, get_class($this), '$index = ' . $index, __FUNCTION__);
						continue;
					}

					//saving the order
					$orderDate = new UDate(trim($order->created_at), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_TIMEZONE));
					$orderDate->setTimeZone('UTC');
					$shippingAddr = $billingAddr = null;
					if(($o = Order::getByOrderNo(trim($order->increment_id))) instanceof Order){
						//skip, if order exsits
						self::_log('Found order from DB, ID = ' . $o->getId(), self::LOG_TYPE, 0, get_class($this), '$index = ' . $index, __FUNCTION__);
						continue;
					}

					$o = new Order();
					self::_log('Found no order from DB, create new', self::LOG_TYPE, 0, get_class($this), '$index = ' . $index, __FUNCTION__);
					$customer = Customer::create(
							(isset($order->billing_address) && isset($order->billing_address->company) && trim($order->billing_address->company) !== '') ? trim($order->billing_address->company) : (isset($order->customer_firstname) ? trim($order->customer_firstname) . ' ' . trim($order->customer_lastname) : ''),
							'',
							trim($order->customer_email),
							$this->_createAddr($order->billing_address, $billingAddr),
							true,
							'',
							$this->_createAddr($order->shipping_address, $shippingAddr),
							isset($order->customer_id) ? trim($order->customer_id) : 0
					);

					$o->setOrderNo(trim($order->increment_id))
						->setOrderDate(trim($orderDate))
						->setTotalAmount(trim($order->grand_total))
						->setStatus((strtolower($status) === 'canceled' ? OrderStatus::get(OrderStatus::ID_CANCELLED) : OrderStatus::get(OrderStatus::ID_NEW)))
						->setIsFromB2B(true)
						->setShippingAddr($customer->getShippingAddress())
						->setBillingAddr($customer->getBillingAddress())
						->setCustomer($customer)
						->save();
					self::_log('Saved the order, ID = ' . $o->getId(), self::LOG_TYPE, 0, get_class($this), '$index = ' . $index, __FUNCTION__);
					$totalShippingCost = StringUtilsAbstract::getValueFromCurrency(trim($order->shipping_amount)) * 1.1;
					//create order info
					$this->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_CUS_NAME), trim($customer->getName()))
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_CUS_EMAIL), trim($customer->getEmail()))
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_QTY_ORDERED), intval(trim($order->total_qty_ordered)))
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_STATUS), trim($order->status))
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_STATE), trim($order->state))
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_TOTAL_AMOUNT), trim($order->grand_total))
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD), trim($order->shipping_description))
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_SHIPPING_COST), $totalShippingCost)
						->_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_PAYMENT_METHOD), (!isset($order->payment) ? '' : (!isset($order->payment->method) ? '' : trim($order->payment->method))));
					self::_log('Updated order info', self::LOG_TYPE, 0, get_class($this), '$index = ' . $index, __FUNCTION__);

					//saving the order item
					$totalItemCost = 0;
					foreach($order->items as $item)	{
						$this->_createItem($o, $item);
						$totalItemCost = $totalItemCost * 1 + StringUtilsAbstract::getValueFromCurrency($item->row_total) * 1.1;
					}
					if(($possibleSurchargeAmount = ($o->getTotalAmount() - $totalShippingCost - $totalItemCost)) > 0 && ($product = Product::getBySku('surcharge')) instanceof Product) {
						OrderItem::create($o, $product,	$possibleSurchargeAmount, 1, $possibleSurchargeAmount);
					}
					//record the last imported time for this import process
					SystemSettings::addSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME, trim($order->created_at));
					self::_log('Updating the last updated time :' . trim($order->created_at), self::LOG_TYPE, 0, get_class($this), '', __FUNCTION__);
					$totalItems++;
				}

				if($transStarted === false)
					Dao::commitTransaction();
			} catch(Exception $e) {
				if($transStarted === false)
					Dao::rollbackTransaction();
				throw $e;
			}
		}

		self::_log($lastUpdatedTime . " => " . SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME) . ' => ' . $totalItems, self::LOG_TYPE, 0, get_class($this), '', __FUNCTION__);
		return $this;
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
		if(count(OrderInfo::find($order, $type)) > 0 ) {
			foreach($items as $item)
				OrderInfo::create($order, $type, $value, $item);
		} else {
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
		return Address::create(isset($addressObj->street) ? preg_replace('/\s+/', ', ', trim($addressObj->street)) : '',
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
	private static function _createItem(Order $order, $itemObj)
	{
		$productXml = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG, $this->_getWSDL(), $this->_getApiUser(), $this->_getApiKey())->getProductInfo(trim($itemObj->sku));

		$product = Product::create(trim($itemObj->sku), trim($itemObj->name), trim($itemObj->product_id));
		if(($updateOptions = trim($itemObj->product_options)) !== '' && is_array($updateOptions = unserialize($updateOptions))) {
			if(isset($updateOptions['options'])) {
				$stringArray = array();
				foreach($updateOptions['options'] as $option) {
					$stringArray[] = '<b>' . trim($option['label']) . '</b>';
					$stringArray[] = trim($option['print_value']);
					$stringArray[] = '';
				}
				$updateOptions = '<br />' . implode('<br />', $stringArray);
			} else {
				$updateOptions = '';
			}
		}
		return OrderItem::create($order,
				$product,
				trim($itemObj->price) * 1.1,
				trim($itemObj->qty_ordered),
				trim($itemObj->row_total) * 1.1,
				trim($itemObj->item_id),
				null,
				$product->getName() . $updateOptions
		);
	}
	/**
	 * Getting the list of lastest updated orders
	 *
	 * @param string $lastUpdatedTime The datatime string
	 *
	 * @return array
	 */
	public static function getLastestOrders($lastUpdatedTime)
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
		return self::_connect()->salesOrderList(self::$_sessionId, $params);
	}

	/**
	 * Getting the information of an order
	 *
	 * @param string $orderId The id of the order
	 *
	 * @return array
	 */
	public static function getOrderInfo($orderId)
	{
		return self::_connect()->salesOrderInfo(self::$_sessionId, $orderId);
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
	public static function changeOrderStatus(Order $order, $orderStatus, $comments = '', $notifCustomer = false)
	{
		return self::_connect()->salesOrderAddComment(self::$_sessionId, $order->getOrderNo(), $orderStatus, $comments, $notifCustomer);
	}
	/**
	 * Adding comments to a order in Magento
	 *
	 * @param Order  $order         The order
	 * @param string $comments      The new comments fo the order
	 * @param bool   $notifCustomer Whether we want to notify the customer
	 */
	public static function addComments(Order $order, $comments = '', $notifCustomer = false)
	{
		return self::_connect()->salesOrderAddComment(self::$_sessionId, $order->getOrderNo(), $order->getInfo(OrderInfoType::ID_MAGE_ORDER_STATUS), $comments, $notifCustomer);
	}
	/**
	 * Hold an order
	 *
	 * @param Order  $order         The order
	 *
	 * @return bool Whether the action has done successfully
	 */
	public static function holdOrder(Order $order)
	{
		return self::_connect()->salesOrderHold(self::$_sessionId, $order->getOrderNo());
	}
	/**
	 * unHold an order
	 *
	 * @param Order  $order         The order
	 *
	 * @return bool Whether the action has done successfully
	 */
	public static function unHoldOrder(Order $order)
	{
		return self::_connect()->salesOrderUnhold(self::$_sessionId, $order->getOrderNo());
	}
	/**
	 * Cancel an order
	 *
	 * @param Order  $order         The order
	 *
	 * @return bool Whether the action has done successfully
	 */
	public static function cancelOrder(Order $order)
	{
		return self::_connect()->salesOrderCancel(self::$_sessionId, $order->getOrderNo());
	}
}