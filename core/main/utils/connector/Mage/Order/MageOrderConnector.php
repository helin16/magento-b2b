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
		$class = get_called_class();

		$totalItems = 0;
		self::_log('starting ...', self::LOG_TYPE, 0, $class, 'start', __FUNCTION__);
		if(($lastUpdatedTime = trim($lastUpdatedTime)) === '') {
			self::_log('Getting the last updated time', self::LOG_TYPE, 0, $class, '$lastUpdatedTime is blank', __FUNCTION__);
			$lastUpdatedTime = trim(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME));
		}

		//getting the lastest order since last updated time
		$orders = self::getLastestOrders($lastUpdatedTime);
		self::_log('Found ' . count($orders) . ' order(s) since "' . $lastUpdatedTime . '".', self::LOG_TYPE, 0, $class, '', __FUNCTION__);
		if(is_array($orders) && count($orders) > 0) {
			$transStarted = false;
			try {
				try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}

				self::_log('=== START LOOPING ' . count($orders) . 'order(s)', self::LOG_TYPE, 0, $class, '', __FUNCTION__);
				foreach($orders as $index => $order) {
					$orderNo = trim($order->increment_id);
					self::_log('Trying to getOrderInfo from Magento with orderNo = ' . $orderNo . '.', self::LOG_TYPE, 0, $class, '', __FUNCTION__, self::_getPreFix(1));

					$order = self::getOrderInfo($orderNo);
					if(!is_object($order)) {
						self::_log('Found no object from $order, next element!', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(1));
						self::_log('');
						continue;
					}
					if(($status = trim($order->state)) === '') {
						self::_log('Found no state Elment from $order, next element!', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(1));
						self::_log('');
						continue;
					}

					$orderDate = new UDate(trim($order->created_at), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_TIMEZONE));
					$orderDate->setTimeZone('UTC');
					$shippingAddr = $billingAddr = null;
					if(($o = Order::getByOrderNo($orderNo)) instanceof Order){
						//skip, if order exsits
						self::_log('Found order from DB, ID = ' . $o->getId(), self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(1));
						self::_log('');
						continue;
					}

					$o = new Order();
					self::_log('-- Creating a new Order for orderNo = ' . $orderNo . ' ------------', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(1));
					$customer = Customer::create(
							(isset($order->billing_address) && isset($order->billing_address->company) && trim($order->billing_address->company) !== '') ? trim($order->billing_address->company) : (isset($order->customer_firstname) ? trim($order->customer_firstname) . ' ' . trim($order->customer_lastname) : ''),
							'',
							trim($order->customer_email),
							self::_createAddr($order->billing_address, $billingAddr),
							true,
							'',
							self::_createAddr($order->shipping_address, $shippingAddr),
							isset($order->customer_id) ? trim($order->customer_id) : 0
					);
					self::_log('Got a customer, ID=' . $customer->getId(), self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(2));
					$o->setOrderNo(trim($order->increment_id))
						->setOrderDate(trim($orderDate))
						->setTotalAmount(trim($order->grand_total))
						->setStatus((strtolower($status) === 'canceled' ? OrderStatus::get(OrderStatus::ID_CANCELLED) : OrderStatus::get(OrderStatus::ID_NEW)))
						->setIsFromB2B(true)
						->setShippingAddr($customer->getShippingAddress())
						->setBillingAddr($customer->getBillingAddress())
						->setCustomer($customer)
						->save();
					self::_log('!!!! Saved the order, ID = ' . $o->getId() . ', Start creating Order Infos: ', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(2));

					//create order info
					$totalShippingCost = StringUtilsAbstract::getValueFromCurrency(trim($order->shipping_amount)) * 1.1;
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_CUS_NAME), trim($customer->getName()), self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_CUS_EMAIL), trim($customer->getEmail()), self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_QTY_ORDERED), intval(trim($order->total_qty_ordered)), self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_STATUS), trim($order->status), self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_STATE), trim($order->state), self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_TOTAL_AMOUNT), trim($order->grand_total), self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD), trim($order->shipping_description), self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_SHIPPING_COST), $totalShippingCost, self::_getPreFix(3));
					self::_createOrderInfo($o, OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_PAYMENT_METHOD), (!isset($order->payment) ? '' : (!isset($order->payment->method) ? '' : trim($order->payment->method))), self::_getPreFix(3));
					self::_log('Updated All Order Info(s).', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(2));
					self::_log('');

					//saving the order item
					$orderItems = $order->items;
					self::_log('** Start Creating (' . count($orderItems) . ') Order Items ****', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(2));
					$totalItemCost = 0;
					foreach($orderItems as $item)	{
						self::_createItem($o, $item, self::_getPreFix(3));
						self::_log('');
						$totalItemCost = $totalItemCost * 1 + StringUtilsAbstract::getValueFromCurrency($item->row_total) * 1.1;
					}
					self::_log('** Finished Creating (' . count($orderItems) . ') Order Items ****', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(2));
					self::_log('');

					self::_log('== Setting check whether there is a surcharge of the order ========', self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(2));
					if(($possibleSurchargeAmount = ($o->getTotalAmount() - $totalShippingCost - $totalItemCost)) > 0 && ($product = Product::getBySku('surcharge')) instanceof Product) {
						OrderItem::create($o, $product,	$possibleSurchargeAmount, 1, $possibleSurchargeAmount);
						self::_log(' %% created a surcharge ' . StringUtilsAbstract::getCurrency($possibleSurchargeAmount), self::LOG_TYPE, 0, $class, '$index = ' . $index, __FUNCTION__, self::_getPreFix(3));
					}
					self::_log('');

					//record the last imported time for this import process
					SystemSettings::addSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME, trim($order->created_at));
					self::_log('## Updating the last updated time :' . trim($order->created_at) . '##################', self::LOG_TYPE, 0, $class, '', __FUNCTION__, self::_getPreFix(2));
					self::_log('');
					$totalItems++;
				}

				self::_log('', self::LOG_TYPE, 0, $class, '', __FUNCTION__);
				if($transStarted === false)
					Dao::commitTransaction();
			} catch(Exception $e) {
				if($transStarted === false)
					Dao::rollbackTransaction();
				throw $e;
			}
		}

		self::_log($lastUpdatedTime . " => " . SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_LAST_IMPORT_TIME) . ' => ' . $totalItems, self::LOG_TYPE, 0, $class, '', __FUNCTION__);
		return $totalItems;
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
	private static function _createOrderInfo(Order $order, OrderInfoType $type, $value, $preFix = '')
	{
		$class = get_called_class();
		self::_log('Creating an order info[' . $type->getName() . ', ID=' . $type->getId() . '] for OrderNo=' . $order->getOrderNo(), self::LOG_TYPE, 0, $class, '', __FUNCTION__, $preFix);
		$items = OrderInfo::find($order, $type);
		$itemIds = array();
		if(count($items) > 0 ) {
			foreach($items as $item) {
				$orderInfo = OrderInfo::create($order, $type, $value, $item);
				$itemIds[] = $orderInfo->getId();
			}
		} else {
			$items = array($orderInfo = OrderInfo::create($order, $type, $value));
			$itemIds[] = $orderInfo->getId();
		}
		self::_log('Created OrderInfo, IDs: ' . implode(', ', $itemIds), self::LOG_TYPE, 0, $class, '', __FUNCTION__, $preFix);
		return $items;
	}
	/**
	 * Creating an address ojbect from Magento
	 *
	 * @param stdClass $addressObj The stdclass of the address object
	 *
	 * @return Address
	 */
	private static function _createAddr($addressObj, &$exsitAddr = null)
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
	private static function _createItem(Order $order, $itemObj, $preFix = '')
	{
		$class = get_called_class();
		$sku = trim($itemObj->sku);
		self::_log('Trying to find productXml from Magento, for sku: ' . $sku, $class::LOG_TYPE, 0, $class, '', __FUNCTION__, $preFix);

		$mageProduct = MageProductConnector::getProductInfo($sku);
		$class::_log('Got result from Mage: ' . preg_replace("/[\n\r]/", "\n\t\t\t\t", print_r($mageProduct, true)), $class::LOG_TYPE, 0, $class, '', __FUNCTION__, $preFix);

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

		$orderItem = OrderItem::create($order,
				$product,
				trim($itemObj->price) * 1.1,
				trim($itemObj->qty_ordered),
				trim($itemObj->row_total) * 1.1,
				trim($itemObj->item_id),
				null,
				$product->getName() . $updateOptions
		);
		$class::_log('Created OrderItem, ID: ' . $orderItem->getId(), $class::LOG_TYPE, 0, $class, '', __FUNCTION__, $preFix);
		return $orderItem;
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