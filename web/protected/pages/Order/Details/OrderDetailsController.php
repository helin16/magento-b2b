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
		if(!($order = FactoryAbastract::service('Order')->get($this->Request['orderId'])) instanceof Order)
			die('Invalid Order!');
		$js = parent::_getEndJs();
		$orderItems = $courierArray = $paymentMethodArray = array();
		foreach($order->getOrderItems() as $orderItem)
			$orderItems[] = $orderItem->getJson();
		$purchaseEdit = $warehouseEdit = $accounEdit = $statusEdit = 'false';
		if($order->canEditBy(Core::getRole()))
		{
			$statusEdit = ($order->canEditBy(FactoryAbastract::service('Role')->get(Role::ID_STORE_MANAGER)) || $order->canEditBy(FactoryAbastract::service('Role')->get(Role::ID_SYSTEM_ADMIN))) ? 'true' : 'false';
			if(in_array(intval(Core::getRole()->getId()), array(Role::ID_SYSTEM_ADMIN, Role::ID_STORE_MANAGER)))
				$purchaseEdit = $warehouseEdit = $accounEdit = 'true';
			else
			{
				if(trim(Core::getRole()->getId()) === trim(Role::ID_PURCHASING))
					$purchaseEdit = 'true';
				else if(trim(Core::getRole()->getId()) === trim(Role::ID_WAREHOUSE))
					$warehouseEdit = 'true';
				else if(trim(Core::getRole()->getId()) === trim(Role::ID_ACCOUNTING))
					$accounEdit = 'true';
			}
		}
		$orderStatuses = array_map(create_function('$a', 'return $a->getJson();'), OrderStatus::findAll());
		$courierArray = array_map(create_function('$a', 'return $a->getJson();'), Courier::findAll());
		$paymentMethodArray = array_map(create_function('$a', 'return $a->getJson();'), PaymentMethod::findAll());
		$js .= 'pageJs';
			$js .= '.setCallbackId("updateOrder", "' . $this->updateOrderBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("addComments", "' . $this->addCommentsBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("confirmPayment", "' . $this->confirmPaymentBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("changeOrderStatus", "' . $this->changeOrderStatusBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("updateOIForWH", "' . $this->updateOIForWHBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("updateShippingInfo", "' . $this->updateShippingInfoBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("getPaymentDetails", "' . $this->getPaymentDetailsBtn->getUniqueID() . '")';
			$js .= '.setCallbackId("clearETA", "' . $this->clearETABtn->getUniqueID() . '")';
			$js .= '.setCallbackId("changeIsOrdered", "' . $this->changeIsOrderedBtn->getUniqueID() . '")';
			$js .= '.setEditMode(' . $purchaseEdit . ', ' . $warehouseEdit . ', ' . $accounEdit . ', ' . $statusEdit . ')';
			$js .= '.setOrder('. json_encode($order->getJson()) . ', ' . json_encode($orderItems) . ', ' . json_encode($orderStatuses) . ')';
			$js .= '.setCourier('. json_encode($courierArray) . ')';
			$js .= '.setPaymentMethods('. json_encode($paymentMethodArray) . ')';
			$js .= '.setCommentType("'. Comments::TYPE_PURCHASING . '", "' . Comments::TYPE_WAREHOUSE . '")';
			$js .= '.setOrderStatusIds(['. OrderStatus::ID_NEW . ', ' . OrderStatus::ID_INSUFFICIENT_STOCK . '], ['. OrderStatus::ID_ETA . ', ' . OrderStatus::ID_STOCK_CHECKED_BY_PURCHASING . '])';
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
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->for) || ($for = trim($params->CallbackParameter->for)) === '')
				throw new Exception('System Error: invalid for passed in!');
			$notifyCustomer = isset($params->CallbackParameter->notifyCustomer) && intval($params->CallbackParameter->notifyCustomer) === 1 ? true : false;
			if(!$order->canEditBy(Core::getRole()))
				throw new Exception('You do NOT edit this order as ' . Core::getRole() . '!');
			$hasETA = false;
			$allPicked = true;
			$commentType = ($for === Comments::TYPE_PURCHASING ? Comments::TYPE_PURCHASING : Comments::TYPE_WAREHOUSE);
			foreach($params->CallbackParameter->items as $orderItemId => $obj)
			{
				if(!($orderItem = FactoryAbastract::service('OrderItem')->get($orderItemId)) instanceof OrderItem)
					throw new Exception ("System Error: invalid order item(ID=" . $orderItemId . ')');
				$sku = $orderItem->getProduct()->getSku();
				$comments = isset($obj->comments) ? trim($obj->comments) : '';
				if($for === Comments::TYPE_PURCHASING) //purchasing
				{
					if(($hasStock = (trim($obj->hasStock) === '1' ? true : false)) === true)
					{
						$orderItem->setIsOrdered(false);
						$orderItem->setEta(trim(UDate::zeroDate()));
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
							$order->addComment('Added ETA[' . $eta . '] for product(SKU=' . $sku .'): ' . $comments, $commentType);
							$orderItem->addComment($comments, $commentType);
						}
						$hasETA = true;
					}
					$orderItem->setIsPicked(false);
				}
				else if ($for === Comments::TYPE_WAREHOUSE) //warehouse
				{
					if(isset($obj->isPicked))
					{
						$picked = (trim($obj->isPicked) === '1');
						$orderItem->setIsPicked($picked);
						$order->addComment(($picked ? '' : 'NOT ') . 'Picked product(SKU=' . $sku .'): ' . $comments, $commentType);
						$orderItem->addComment($comments, $commentType);
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
				}
				FactoryAbastract::service('OrderItem')->save($orderItem);
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
			}
			if($for === Comments::TYPE_WAREHOUSE)
			{
				if($allPicked === true)
					$order->setStatus(OrderStatus::get(OrderStatus::ID_PICKED));
				else
					$order->setStatus(OrderStatus::get(OrderStatus::ID_INSUFFICIENT_STOCK));
				$order->addComment('Changed from [' . $status . '] to [' . $order->getStatus() . ']', Comments::TYPE_SYSTEM);
			}
			FactoryAbastract::service('Order')->save($order);
			
			//notify customer
			if($notifyCustomer === true)
			{
				$notificationMsg = trim(OrderNotificationTemplateControl::getMessage($order->getStatus()->getName(), $order));
				if($notificationMsg !== '')
				{
					B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
						SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
						)->changeOrderStatus($order, $order->getStatus()->getMageStatus(), $notificationMsg, true);
					$order->addComment('An email notification has been sent to customer for: ' . $order->getStatus()->getName(), Comments::TYPE_SYSTEM);
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
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
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
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->orderStatusId) || !($orderStatus = OrderStatus::get($params->CallbackParameter->orderStatusId)) instanceof OrderStatus)
				throw new Exception('System Error: invalid orderStatus passed in!');
			if(!isset($params->CallbackParameter->comments) || ($comments = trim($params->CallbackParameter->comments)) === '')
				throw new Exception('System Error: comments not provided!');
			
			$oldStatus = $order->getStatus();
			$order->setStatus($orderStatus);
			$order->addComment('change Status from [' . $oldStatus. '] to [' . $order->getStatus() . ']: ' . $comments, Comments::TYPE_NORMAL);
			FactoryAbastract::service('Order')->save($order);
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
		$commentString = "";
		
		try 
		{
			Dao::beginTransaction();
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!isset($params->CallbackParameter->paidAmt) || ($paidAmount = trim($params->CallbackParameter->paidAmt)) === '' || !is_numeric($paidAmount))
				throw new Exception('System Error: invalid Paid Amount passed in!');
			if(!isset($params->CallbackParameter->paymentMethod) || ($paymentMethodId = trim($params->CallbackParameter->paymentMethod)) === '' || !($paymentMethod = FactoryAbastract::dao('PaymentMethod')->findById($paymentMethodId)) instanceof PaymentMethod)
				throw new Exception('System Error: invalid Payment Method passed in!');
			if(!isset($params->CallbackParameter->amtDiff) || ($amountDiff = trim($params->CallbackParameter->amtDiff)) === '' || !is_numeric($amountDiff))
				throw new Exception('System Error: Invalid Amount Difference passed in!');
			if(!isset($params->CallbackParameter->extraComment))
				throw new Exception('System Error: Invalid Extra Comment passed in!');
			if(($extraComment = trim($params->CallbackParameter->extraComment)) === '' && $amountDiff !== '0')
				throw new Exception('Additional Comment is Mandatory as the Paid Amount is not mathcing with the Total Amount!');
			
			$payment = new Payment();
			$payment->setOrder($order);
			$payment->setMethod($paymentMethod);
			$payment->setValue($paidAmount);
			$payment->setActive(true);
			FactoryAbastract::dao('Payment')->save($payment);
			
 			$order->setTotalPaid($paidAmount);
 			$order->setPassPaymentCheck(true);
 			FactoryAbastract::service('Order')->save($order);
			
			$commentString = "Total Amount Due was $" . number_format($order->getTotalAmount(), 2, '.', ',') . ". And total amount paid is $" . number_format($paidAmount, 2, '.', ',') . ". Payment Method is " . $paymentMethod->getName();
			if(($amtDiff = $order->getTotalAmount() - $paidAmount) === 0)
				$commentString = "Amount is fully paid.".$commentString.". Payment method is ".$paymentMethod->getName();
			$commentString = '['.$commentString.']'.($extraComment !== '' ? ' : '.$extraComment : '');
			
			$comment = Comments::addComments($order, $commentString, Comments::TYPE_ACCOUNTING);
			
			//notify the customer
			$notificationMsg = trim(OrderNotificationTemplateControl::getMessage('paid', $order));
			if($notificationMsg !== '')
			{
				B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
					)->changeOrderStatus($order, FactoryAbastract::service('OrderStatus')->get(OrderStatus::ID_PICKED)->getMageStatus(), $notificationMsg, true);
				$order->addComment('An email notification contains payment checked info has been sent to customer for: ' . $order->getStatus()->getName(), Comments::TYPE_SYSTEM);
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
			if(!isset($params->CallbackParameter->order) || !($order = Order::get($params->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			if(!$order->getStatus() instanceof OrderStatus || trim($order->getStatus()->getId()) !== trim(OrderStatus::ID_PICKED))
				throw new Exception('System Error: Order ['.$order->getOrderNo().'] Is Not is PICKED status. Current status is ['.($order->getStatus() instanceof OrderStatus ? $order->getStatus()->getName() : 'NULL').']');
			if(!isset($params->CallbackParameter->shippingInfo))
				throw new Exception('System Error: invalid Shipping Info Details passed in!');
			$validColumns = array('courierId', 'contactNo', 'contactName', 'street', 'city', 'region', 'country', 'postCode', 'noOfCartons', 'conNoteNo', 'actualShippingCost', 'estShippingCost');
			$shippingInfoArray = $params->CallbackParameter->shippingInfo;
			foreach($validColumns as $col)
			{
				if(!isset($shippingInfoArray->$col))
					throw new Exception('System Error: Incomplete Shipping Info Details(' . $col . ') provided!!!');
			}
			if(!($courier = FactoryAbastract::service('Courier')->get($shippingInfoArray->courierId)) instanceof Courier)
				throw new Exception('Invalid Courier Id [' . $shippingInfoArray->courierId . '] provided');
			
			$contactName = $shippingInfoArray->contactName;
			$contactNo = $shippingInfoArray->contactNo;
			$shippingAddress = Address::create(
					trim($shippingInfoArray->street), 
					trim($shippingInfoArray->city), 
					trim($shippingInfoArray->region), 
					trim($shippingInfoArray->country), 
					trim($shippingInfoArray->postCode), 
					trim($contactName[0]), 
					trim($contactNo[0])
			);
			$shipment = Shippment::create(
					$shippingAddress, 
					$courier, 
					trim($shippingInfoArray->conNoteNo), //$consignmentNo, 
					new UDate("now"), 
					$order, 
					$contactName, 
					trim($contactNo), // $contactNo = '' , 
					trim($shippingInfoArray->noOfCartons), //$noOfCartons = 0, 
					trim($shippingInfoArray->estShippingCost), //$estShippingCost = '0.00', 
					trim($shippingInfoArray->actualShippingCost), //$actualShippingCost = '0.00', 
					(isset($shippingInfoArray->deliveryInstructions) ? trim($shippingInfoArray->deliveryInstructions) : '') //$deliveryInstructions = ''
			);
			
			$order->setStatus(FactoryAbastract::service('OrderStatus')->get(OrderStatus::ID_SHIPPED));
			FactoryAbastract::service('Order')->save($order);
			$result['shipment'] = $shipment->getJson();
			
			//add shipment information
// 			$templateName = (trim($shipment->getCourier()->getId()) === trim(Courier::ID_LOCAL_PICKUP) ? 'local_pickup' : $order->getStatus()->getName());
// 			$notificationMsg = trim(OrderNotificationTemplateControl::getMessage($templateName, $order));
// 			if($notificationMsg !== '')
// 			{
// 				B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_SHIP,
// 					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
// 					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
// 					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
// 					)
// 					->shipOrder($order, $shipment, array(), $notificationMsg, false, false);
					
// 				//push the status of the order to SHIPPed
// 				B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
// 					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
// 					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
// 					SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
// 					)->changeOrderStatus($order, $order->getStatus()->getMageStatus(), $notificationMsg, true);
// 				$order->addComment('An email notification contains shippment information has been sent to customer for: ' . $order->getStatus()->getName(), Comments::TYPE_SYSTEM);
// 			}
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$error[] = $ex->getMessage();
		}
		
		$params->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
	
	public function getPaymentDetailsForOrder($sender, $param)
	{
		$result = $error = array();
		try 
		{
			$result['items'] = array();
			
			if(!isset($param->CallbackParameter->order) || !($order = Order::get($param->CallbackParameter->order->orderNo)) instanceof Order)
				throw new Exception('System Error: invalid order passed in!');
			
			$paymentArray = FactoryAbastract::service('Payment')->findByCriteria('orderId = ?', array($order->getId()), true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array("py.updated" => "desc"));
			foreach($paymentArray as $payment)
				$result['items'][] = $payment->getJson();	
		}
		catch(Exception $ex)
		{	
			$error[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($result, $error);
	}
	
	public function clearETA($sender, $param)
	{
		$results = $errors = array();
		try
		{
			if(!isset($param->CallbackParameter->item_id) || !($item = FactoryAbastract::service('OrderItem')->get($param->CallbackParameter->item_id)) instanceof OrderItem)
				throw new Exception('System Error: invalid order item provided!');
				
			if(!isset($param->CallbackParameter->comments) || ($comments = trim($param->CallbackParameter->comments)) === '')
				$comments = '';
				
			Dao::beginTransaction();
			
			//saving the order item
			$item->setETA(UDate::zeroDate());
			$item->addComment('Clearing the ETA: ' . $comments);
			$order = $item->getOrder();
			$sku = $item->getProduct()->getSku();
			$order->addComment('Clearing the ETA for product (' . $sku . '): ' . $comments, Comments::TYPE_PURCHASING);
			FactoryAbastract::service('OrderItem')->save($item);
			
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
			FactoryAbastract::service('Order')->save($order);
			
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
			if(!isset($param->CallbackParameter->item_id) || !($item = FactoryAbastract::service('OrderItem')->get($param->CallbackParameter->item_id)) instanceof OrderItem)
				throw new Exception('System Error: invalid order item provided!');
		
			if(!isset($param->CallbackParameter->isOrdered))
				throw new Exception('System Error: invalid order item: isOrdered needed!');
			$setIsOrdered = intval($param->CallbackParameter->isOrdered);
	
			Dao::beginTransaction();
			$item->setIsOrdered($setIsOrdered);
			$item->addComment('Changing the isOrdered to be : ' . ($setIsOrdered === 1 ? 'ORDERED' : 'NOT ORDERED'));
		
			$order = $item->getOrder();
			$sku = $item->getProduct()->getSku();
		
			$order->addComment('Changing the isOrdered for product (sku=' . $sku . ') to be : ' . ($setIsOrdered === 1 ? 'ORDERED' : 'NOT ORDERED'), Comments::TYPE_PURCHASING);
			FactoryAbastract::service('OrderItem')->save($item);
		
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
	
}
?>
