<?php
/**
 * This is the OrderDetailsController
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class OrderDetailsController extends BPCPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'order';
	/**
	 * Getting The end javascript
	 *
	 * @return string
	 */
	protected function _getEndJs()
	{
		if(!($order = Order::get($this->Request['orderId'])) instanceof Order)
			die('Invalid Order!');
		if(trim($order->getType()) !== Order::TYPE_INVOICE) {
			header('Location: /order/'. $order->getId() . '.html?' . $_SERVER['QUERY_STRING']);
			die();
		}
		
		$js = parent::_getEndJs();
		$orderItems = $courierArray = $paymentMethodArray = array();
		foreach($order->getOrderItems() as $orderItem)
			$orderItems[] = $orderItem->getJson();
		$purchaseEdit = $warehouseEdit = $accounEdit = $statusEdit = 'false';
		if($order->canEditBy(Core::getRole()))
		{
			$statusEdit = ($order->canEditBy(Role::get(Role::ID_STORE_MANAGER)) || $order->canEditBy(Role::get(Role::ID_SYSTEM_ADMIN))) ? 'true' : 'false';
			if(in_array(intval(Core::getRole()->getId()), array(Role::ID_SYSTEM_ADMIN, Role::ID_STORE_MANAGER, Role::ID_SALES)))
				$purchaseEdit = $warehouseEdit = $accounEdit = 'true';
			else
			{
				if(trim(Core::getRole()->getId()) === trim(Role::ID_PURCHASING))
					$purchaseEdit = 'true';
				else if(trim(Core::getRole()->getId()) === trim(Role::ID_WAREHOUSE))
					$warehouseEdit = 'true';
			}
		}
		if(in_array(intval(Core::getRole()->getId()), array(Role::ID_SYSTEM_ADMIN, Role::ID_STORE_MANAGER, Role::ID_ACCOUNTING)))
			$accounEdit = 'true';

		$orderStatuses = array_map(create_function('$a', 'return $a->getJson();'), OrderStatus::findAll());
		$courierArray = array_map(create_function('$a', 'return $a->getJson();'), Courier::findAll());
		$paymentMethodArray = array_map(create_function('$a', 'return $a->getJson();'), PaymentMethod::findAll());
		$payments = array_map(create_function('$a', 'return $a->getJson();'), $order->getPayments());
		$js .= 'pageJs';
			$js .= '.setCallbackId("updateOrder", "' . $this->updateOrderBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("addComments", "' . $this->addCommentsBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("confirmPayment", "' . $this->confirmPaymentBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("changeOrderStatus", "' . $this->changeOrderStatusBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("updateOIForWH", "' . $this->updateOIForWHBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("updateShippingInfo", "' . $this->updateShippingInfoBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("clearETA", "' . $this->clearETABtn->getUniqueID() . '")';
			$js .= '.setCallbackId("setOrderType", "' . $this->setOrderTypeBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("changeIsOrdered", "' . $this->changeIsOrderedBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("deletePayment", "' . $this->deletePaymentBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("updateAddress", "' . $this->updateAddressBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("sendEmail", "' . $this->sendEmailBtn->getUniqueID() . '")';
			$js .= '.setEditMode(' . $purchaseEdit . ', ' . $warehouseEdit . ', ' . $accounEdit . ', ' . $statusEdit . ')';
			$js .= '.setOrder('. json_encode($order->getJson()) . ', ' . json_encode($orderItems) . ', ' . json_encode($orderStatuses) . ', ' . OrderStatus::ID_SHIPPED . ')';
			$js .= '.setCourier('. json_encode($courierArray) . ', ' . Courier::ID_LOCAL_PICKUP . ')';
			$js .= '.setPaymentMethods('. json_encode($paymentMethodArray) . ')';
			$js .= '.setCommentType("'. Comments::TYPE_PURCHASING . '", "' . Comments::TYPE_WAREHOUSE . '")';
			$js .= '.setOrderStatusIds(['. OrderStatus::ID_NEW . ', ' . OrderStatus::ID_INSUFFICIENT_STOCK . '], ['. OrderStatus::ID_ETA . ', ' . OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING . '], ['. OrderStatus::ID_PICKED . '])';
			$js .= '.setPayments('. json_encode($payments) . ')';
			$js .= '.init("detailswrapper")';
			$js .= '.load();';
		return $js;
	}
	/**
	 *
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function updateOrder($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::getByOrderNo($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->for) || ($for = trim($params->CallbackParameter->for)) === '')
				throw new Exception('System Error: invalid for passed in!');
			$notifyCustomer = isset($params->CallbackParameter->notifyCustomer) && intval($params->CallbackParameter->notifyCustomer) === 1 ? true : false;
			if(!$order->canEditBy(Core::getRole()))
				throw new Exception('You do NOT edit this order as ' . Core::getRole() . '!');
			$hasETA = false;
			$allPicked = true;
			$commentType = ($for === Comments::TYPE_PURCHASING ? Comments::TYPE_PURCHASING : Comments::TYPE_WAREHOUSE);
			$emailBody['productUpdate'] = '<table border="1" style="width:100%">';
			foreach($params->CallbackParameter->items as $orderItemId => $obj)
			{
				if(!($orderItem = OrderItem::get($orderItemId)) instanceof OrderItem)
					throw new Exception ("System Error: invalid order item(ID=" . $orderItemId . ')');
				$commentString = "";
				$sku = $orderItem->getProduct()->getSku();
				$comments = isset($obj->comments) ? trim($obj->comments) : '';
				if($for === Comments::TYPE_PURCHASING) //purchasing
				{
					if(($hasStock = (trim($obj->hasStock) === '1' ? true : false)) === true)
					{
						$orderItem->setIsOrdered(false);
						$orderItem->setEta(trim(UDate::zeroDate()));
						$commentString = 'product(SKU=' . $sku .') marked as in stock';
						$emailBody['productUpdate'] .= '<tr>' . '<td>' . $sku . '</td>' . '<td>' . $orderItem->getProduct()->getName() . '</td>' . '<td>' . 'In Stock' . '</td>';
					}
					else
					{
						$timeZone = trim(SystemSettings::getSettings(SystemSettings::TYPE_SYSTEM_TIMEZONE));
						$now = new UDate('now', $timeZone);
						if(!($eta = new UDate(trim($obj->eta), $timeZone)) instanceof UDate)
							throw new Exception('ETA(=' . trim($obj->eta) . ') is invalid.');
						if($eta->beforeOrEqualTo($now))
							throw new Exception('ETA can NOT be before now(=' . trim($now) . ').');
						$orderItem->setIsOrdered(trim($obj->hasStock) === '1');
						$orderItem->setEta(trim($eta));
						$orderItem->setIsOrdered(trim($obj->isOrdered) === '1');
						if($comments !== '')
						{
							$commentString = 'Added ETA[' . $eta . '] for product(SKU=' . $sku .'): ' . $comments;
							$emailBody['productUpdate'] .= '<tr>' . '<td>' . $sku . '</td>' . '<td>' . $orderItem->getProduct()->getName() . '</td>' . '<td>' . 'ETA: ' . $eta->format('d/M/Y') . '</td>';
						}
						$hasETA = true;
					}
					$orderItem->setIsPicked(false);
				}
				else if ($for === Comments::TYPE_WAREHOUSE) //warehouse
				{
					$picked = (trim($obj->isPicked) === '1') ? true : false;
					$orderItem->setIsPicked($picked);
					$commentString = ($picked ? '' : 'NOT ') . 'Picked product(SKU=' . $sku .'): ' . $comments;
					$emailBody['productUpdate'] .= '<tr>' . '<td>' . $sku . '</td>' . '<td>' . $orderItem->getProduct()->getName() . '</td>' . '<td>' . $picked ? 'Picked by Warehouse' : '' . '</td>';
					if($picked === true) //clear ETA
					{
						$orderItem->setIsOrdered(false);
						$orderItem->setEta(trim(UDate::zeroDate()));
					}
					else
					{
						$orderItem->setEta('');
						$allPicked = false;
					}
				}
				$emailBody['productUpdate'] .= '</table>';
				$commentString .= ($notifyCustomer === true ? ' [NOTIFICATION SENT TO CUSTOMER]' : '');
				$order->addComment($commentString, $commentType);
				$orderItem->addComment($commentString, $commentType)
					->save();
			}

			//push the status of the order
			$status = trim($order->getStatus());
			if($for === Comments::TYPE_PURCHASING)
			{
				if($hasETA === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_ETA));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING));
				$order->addComment('Changed from [' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_SYSTEM);
				$emailBody['orderUpdate'] = 'Order status changed from [' . $status . '] to [' . $order->getStatus() . ']';
			}
			else if($for === Comments::TYPE_WAREHOUSE)
			{
				if($allPicked === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_PICKED));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_INSUFFICIENT_STOCK));
				$order->addComment('Changed from [' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_SYSTEM);
				$emailBody['orderUpdate'] = 'Order status changed from [' . $status . '] to [' . $order->getStatus() . ']';
			}
			$order->save();

			//notify customer
			if($notifyCustomer === true && $order->getIsFromB2B() === true)
			{
				$notificationMsg = trim(OrderNotificationTemplateControl::getMessage($order->getStatus()->getName(), $order));
				if($notificationMsg !== '')
				{
					B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
						)->changeOrderStatus($order, $order->getStatus()->getMageStatus(), $notificationMsg, false);
// 					$emailTitle = 'Your Order ' . $order->getOrderNo() . ' has been updated';
// 					// $order->getCustomer()->getEmail()
// 					EmailSender::addEmail('', 'frank@budgetpc.com.au', $emailTitle, $this->_getNotifictionEmail($order, $emailBody, $emailTitle));
// 					$order->addComment('An email notification has been sent to customer for: ' . $order->getStatus()->getName(), Comments::TYPE_SYSTEM);
				}
			}
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	private function _getNotifictionEmail($order, $emailBody, $emailTitle = '')
	{
		$html = '';
		$html .= '<table cellspacing="0" cellpadding="0" border="0" height="100%" width="100%">';
		$html .= '<tr><td style="padding:20px 0 20px 0" align="center" valign="top">';
		$html .= '<table style="border:1px solid #E0E0E0;" bgcolor="#FFFFFF" border="0" cellpadding="10" cellspacing="0" width="650">';
		$html .= '<tbody><tr>';
		$html .= '<td valign="top"><a href="http://budgetpc.com.au/index.php/"><img src="http://budgetpc.com.au/media/buyshop/default/New-logo005.png" alt="Budget PC Super Store" style="margin-bottom:10px;" border="0"></a></td>';
		$html .= '</tr><tr><td valign="top">';
		$html .= '<h1 style="font-size:22px; font-weight:normal; line-height:22px; margin:0 0 11px 0;">';
		$html .= 'Dear '. $order->getCustomer()->getName() . '</h1>';
		$html .= '<p style="font-size:12px; line-height:16px; margin:0 0 10px 0;">';
		$html .= $emailTitle;
		$html .= '</p>';
		$html .= '<div>' . $emailBody['productUpdate'] . '</div><br/>';
		$html .= '<p style="font-size:12px; line-height:16px; margin:0;">';
		$html .= 'If you have any questions, please feel free to contact us at ';
		$html .= '<a href="mailto:sales@budgetpc.com.au" style="color:#1E7EC8;">sales@budgetpc.com.au</a>';
		$html .= ' or by phone at +61 3 9541 9000.';
		$html .= '</p></td></tr>';
		$html .= '<tr><td style="background:#EAEAEA; text-align:center;" align="center" bgcolor="#EAEAEA"><center><p style="font-size:12px; margin:0;">Thank you again, <strong></strong></p></center></td></tr>';
		$html .= '</tbody></table></td></tr></table>';
		return $html;
	}
	/**
	 *
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function addComments($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::getByOrderNo($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->comments) || ($comments = trim($params->CallbackParameter->comments)) === '')
				throw new Exception('System Error: invalid comments passed in!');
			$comment = Comments::addComments($order, $comments, Comments::TYPE_NORMAL);
			$results = $comment->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 *
	 * @param unknown $sender
	 * @param unknown $params
	 */
	public function changeOrderStatus($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::getByOrderNo($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->orderStatusId) || !($orderStatus = OrderStatus::get($params->CallbackParameter->orderStatusId)) instanceof OrderStatus)
				throw new Exception('System Error: invalid orderStatus passed in!');
			if(!isset($params->CallbackParameter->comments) || ($comments = trim($params->CallbackParameter->comments)) === '')
				throw new Exception('System Error: comments not provided!');

			$oldStatus = $order->getStatus();
			$order->setStatus($orderStatus);
			$order->addComment('change Status from [' . $oldStatus. '] to [' . $order->getStatus() . ']: ' . $comments, Comments::TYPE_NORMAL)
				->save();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}

	/**
	 *
	 * @param unknown $sender
	 * @param unknown $params
	 * @throws Exception
	 */
	public function confirmPayment($sender, $params)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::getByOrderNo($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->payment) || !isset($params->CallbackParameter->payment->paidAmount) || ($paidAmount = StringUtilsAbstract::getValueFromCurrency(trim($params->CallbackParameter->payment->paidAmount))) === '' || !is_numeric($paidAmount))
				throw new Exception('System Error: invalid Paid Amount passed in!');
			if(!isset($params->CallbackParameter->payment->payment_method_id) || ($paymentMethodId = trim($params->CallbackParameter->payment->payment_method_id)) === '' || !($paymentMethod = PaymentMethod::get($paymentMethodId)) instanceof PaymentMethod)
				throw new Exception('System Error: invalid Payment Method passed in!');
			$extraComment = '';
			if(!isset($params->CallbackParameter->payment->extraComments) || ($extraComment = trim($params->CallbackParameter->payment->extraComments)) === '')
				$extraComment = '';
			$amtDiff = trim(abs(StringUtilsAbstract::getValueFromCurrency($order->getTotalAmount()) - $paidAmount));
			if($extraComment === '' && $amtDiff !== '0')
				throw new Exception('Additional Comment is Mandatory as the Paid Amount is not mathcing with the Total Amount!');
			$notifyCust = (isset($params->CallbackParameter->payment->notifyCust) && intval($params->CallbackParameter->payment->notifyCust) === 1) ? true : false;
			//save the payment
			$order->addPayment($paymentMethod, $paidAmount)
				->setPassPaymentCheck(true)
				->save()
				->addComment(($amtDiff === '0' ? 'FULLY PAID' : '') . '[Total Amount Due was ' . StringUtilsAbstract::getCurrency($order->getTotalAmount()) . ". And total amount paid is " . StringUtilsAbstract::getCurrency($paidAmount) . ". Payment Method is " . $paymentMethod->getName() . ']: ' . ($extraComment !== '' ? ' : ' . $extraComment : ''), Comments::TYPE_ACCOUNTING);

			//notify the customer
			if($notifyCust === true && $order->getIsFromB2B() === true)
			{
				$notificationMsg = trim(OrderNotificationTemplateControl::getMessage('paid', $order));
				if($notificationMsg !== '')
				{
					B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
						)->changeOrderStatus($order, OrderStatus::get(OrderStatus::ID_PICKED)->getMageStatus(), $notificationMsg, true);
					$comments = 'An email notification contains payment checked info has been sent to customer for: ' . $order->getStatus()->getName();
					Comments::addComments($order, $comments, Comments::TYPE_SYSTEM);
				}
			}

			$results['items'] = array_map(create_function('$a', '$a->getJson();'), $order->getPayments());
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}

		$params->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * updating the shipping details
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 */
	public function updateShippingDetails($sender, $params)
	{
		$result = $error = $shippingInfoArray = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::getByOrderNo($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!$order->getStatus() instanceof OrderStatus || trim($order->getStatus()->getId()) !== trim(OrderStatus::ID_PICKED))
				throw new Exception('System Error: Order ['.$order->getOrderNo().'] Is Not is PICKED status. Current status is ['.($order->getStatus() instanceof OrderStatus ? $order->getStatus()->getName() : 'NULL').']');
			if(!isset($params->CallbackParameter->shippingInfo))
				throw new Exception('System Error: invalid Shipping Info Details passed in!');
			$shippingInfo = $params->CallbackParameter->shippingInfo;
			if(!($courier = Courier::get($shippingInfo->courierId)) instanceof Courier)
				throw new Exception('Invalid Courier Id [' . $shippingInfo->courierId . '] provided');
			$notifyCust = (isset($shippingInfo->notifyCust) && intval($shippingInfo->notifyCust) === 1) ? true : false;


			$contactName = $shippingInfo->contactName;
			$contactNo = $shippingInfo->contactNo;
// 			if(($street = trim($shippingInfo->street)) !== '')
			$shippingAddress = Address::create(
					trim($shippingInfo->street),
					trim($shippingInfo->city),
					trim($shippingInfo->region),
					trim($shippingInfo->country),
					trim($shippingInfo->postCode),
					trim($contactName),
					trim($contactNo)
			);
			$shipment = Shippment::create(
					$shippingAddress,
					$courier,
					trim($shippingInfo->conNoteNo), //$consignmentNo,
					new UDate(),
					$order,
					$contactName,
					trim($contactNo), // $contactNo = '' ,
					trim($shippingInfo->noOfCartons), //$noOfCartons = 0,
					'0', //$estShippingCost = '0.00',  //TODO:: need to fetch this from the order
					trim($shippingInfo->actualShippingCost), //$actualShippingCost = '0.00',
					(isset($shippingInfo->deliveryInstructions) ? trim($shippingInfo->deliveryInstructions) : '') //$deliveryInstructions = ''
			);

			$order->setStatus(OrderStatus::get(OrderStatus::ID_SHIPPED))
				->save();
			$result['shipment'] = $shipment->getJson();

			//add shipment information
			if($notifyCust === true && $order->getIsFromB2B() === true)
			{
				$templateName = (trim($shipment->getCourier()->getId()) === trim(Courier::ID_LOCAL_PICKUP) ? 'local_pickup' : $order->getStatus()->getName());
				$notificationMsg = trim(OrderNotificationTemplateControl::getMessage($templateName, $order));
				if($notificationMsg !== '')
				{
					B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_SHIP,
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
						)
						->shipOrder($order, $shipment, array(), $notificationMsg, false, false);

					//push the status of the order to SHIPPed
					B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
						)->changeOrderStatus($order, $order->getStatus()->getMageStatus(), $notificationMsg, true);
					$order->addComment('An email notification contains shippment information has been sent to customer for: ' . $order->getStatus()->getName(), Comments::TYPE_SYSTEM);
				}
			}
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$error[] = $ex->getMessage();
		}

		$params->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}

	public function setOrderType($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$items = array();
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->type) || ($type = trim($param->CallbackParameter->type)) === '')
				throw new Exception('Invalid Type passed in!');
			if(!($order = Order::get(trim($param->CallbackParameter->id))) instanceof Order)
				throw new Exception('Invalid Order passed in!');
			
			if(trim($order->getType()) !== $type && in_array($type, array(Order::TYPE_INVOICE, Order::TYPE_ORDER, Order::TYPE_QUOTE) ) ) {
				$order->setType($type)
					->save();
			}

			$results['item'] = $order->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	public function clearETA($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->item_id) || !($item = OrderItem::get($param->CallbackParameter->item_id)) instanceof OrderItem)
				throw new Exception('System Error: invalid order item provided!');

			if(!isset($param->CallbackParameter->comments) || ($comments = trim($param->CallbackParameter->comments)) === '')
				$comments = '';

			Dao::beginTransaction();

			//saving the order item
			$item->setETA(UDate::zeroDate())
				->addComment('Clearing the ETA: ' . $comments);
			$order = $item->getOrder();
			$sku = $item->getProduct()->getSku();
			$order->addComment('Clearing the ETA for product (' . $sku . '): ' . $comments, Comments::TYPE_PURCHASING);
			$item->save();

			//check to see whether we need to update the order as well
			$allChecked = true;
			foreach($order->getOrderItems() as $orderItems)
			{
				if(trim($orderItems->getETA()) !== trim(UDate::zeroDate()))
					$allChecked = false;
			}
			if($allChecked === true)
			{
				$order->addComment('Auto Push this order status from [' . $order->getStatus() . '] to [' . OrderStatus::ID_ETA . '], as the last ETA cleared', Comments::TYPE_SYSTEM);
				$order->setStatus(OrderStatus::get(OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING));
			}
			$order->save();

			$results = $item->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}


	public function changeIsOrdered($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->item_id) || !($item = OrderItem::get($param->CallbackParameter->item_id)) instanceof OrderItem)
				throw new Exception('System Error: invalid order item provided!');

			if(!isset($param->CallbackParameter->isOrdered))
				throw new Exception('System Error: invalid order item: isOrdered needed!');
			$setIsOrdered = intval($param->CallbackParameter->isOrdered);

			$item->setIsOrdered($setIsOrdered);
			$item->addComment('Changing the isOrdered to be : ' . ($setIsOrdered === 1 ? 'ORDERED' : 'NOT ORDERED'));

			$order = $item->getOrder();
			$sku = $item->getProduct()->getSku();

			$order->addComment('Changing the isOrdered for product (sku=' . $sku . ') to be : ' . ($setIsOrdered === 1 ? 'ORDERED' : 'NOT ORDERED'), Comments::TYPE_PURCHASING);
			$item->save();

			$results = $item->getJson();

			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}

	public function deletePayment($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->paymentId) || !($payment = Payment::get($param->CallbackParameter->paymentId)) instanceof Payment)
				throw new Exception('System Error: invalid payment provided!');

			if(!isset($param->CallbackParameter->reason) || ($reason = trim($param->CallbackParameter->reason)) === '')
				throw new Exception('The reason for the deletion is needed!');

			$comments = 'A payment [Value: ' .  StringUtilsAbstract::getCurrency($payment->getValue()) . ', Method: ' . $payment->getMethod()->getName() . '] is DELETED: ' . $reason;
			$payment->setActive(false)
				->save()
				->addComment($comments, Comments::TYPE_ACCOUNTING);
			$payment->getOrder()
				->addComment($comments, Comments::TYPE_ACCOUNTING);
			$results['item'] = $payment->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * Update the address
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 */
	public function updateAddress($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->orderId) || !($order = Order::get($param->CallbackParameter->orderId)) instanceof Order)
				throw new Exception('System Error: invalid order provided!');
			if(!isset($param->CallbackParameter->id))
				throw new Exception('System Error: invalid address provided!');
			
			if(!isset($param->CallbackParameter->type) || ($type = trim($param->CallbackParameter->type)) === '')
				throw new Exception('System Error: invalid address type provided!');
			$getter = 'get' . ucfirst($type) . 'Addr';
			$address = $order->$getter();
			$originalAddressFull = $address instanceof Address ? $address->getFull() : '';
			$address = Address::create(trim($param->CallbackParameter->street), 
				trim($param->CallbackParameter->city), 
				trim($param->CallbackParameter->region), 
				trim($param->CallbackParameter->country), 
				trim($param->CallbackParameter->postCode), 
				trim($param->CallbackParameter->contactName), 
				trim($param->CallbackParameter->contactNo)
				,$address
			);
			if($address instanceof Address) {
				$setter = 'set' . ucfirst($type) . 'Addr';
				$msg = 'Changed ' . trim($param->CallbackParameter->title) . ' from "' . $originalAddressFull . '" to "' . $address->getFull() . '"';
				$order->$setter($address)
					->save()
					->addComment($msg, Comments::TYPE_NORMAL)
					->addLog($msg, Log::TYPE_SYSTEM);
				$address->addLog($msg, Log::TYPE_SYSTEM);
				$results['item'] = $address->getJson();
			} else {
				$results['item'] = array();
			}
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * Sending the email out
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 *
	 * @throws Exception
	 */
	public function sendEmail($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();

			if(!isset($param->CallbackParameter->orderId) || !($order = Order::get($param->CallbackParameter->orderId)) instanceof Order)
				throw new Exception('System Error: invalid order provided!');
			if(!isset($param->CallbackParameter->emailAddress) || ($emailAddress = trim($param->CallbackParameter->emailAddress)) === '')
				throw new Exception('System Error: invalid emaill address provided!');
			$emailBody = '';
			if(isset($param->CallbackParameter->emailBody) && ($emailBody = trim($param->CallbackParameter->emailBody)) !== '')
				$emailBody = str_replace("\n", "<br />", $emailBody);

			$pdfFile = EntityToPDF::getPDF($order);
			$asset = Asset::registerAsset($order->getOrderNo() . '.pdf', file_get_contents($pdfFile), Asset::TYPE_TMP);
			EmailSender::addEmail('sales@budgetpc.com.au', $emailAddress, 'BudgetPC Order:' . $order->getOrderNo() , (trim($emailBody) === '' ? '' : $emailBody . "<br /><br />") .'Please find attached Order (' . $order->getOrderNo() . ') from Budget PC Pty Ltd.', array($asset));
			$order->addComment('An email sent to "' . $emailAddress . '" with the attachment: ' . $asset->getAssetId(), Comments::TYPE_SYSTEM);
			$results['item'] = $order->getJson();

			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
