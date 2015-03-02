<?php
/**
 * Entity for Order
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Order extends InfoEntityAbstract
{
	const TYPE_QUOTE = 'QUOTE';
	const TYPE_ORDER = 'ORDER';
	const TYPE_INVOICE = 'INVOICE';
	/**
	 * The order No from magento
	 *
	 * @var string
	 */
	private $orderNo = '';
	/**
	 * The type of the order: quote, order, invoice
	 *
	 * @var string
	 */
	private $type = self::TYPE_ORDER;
	/**
	 * The order date from magento
	 *
	 * @var UDate
	 */
	private $orderDate;
	/**
	 * The invoice Number
	 *
	 * @var string
	 */
	private $invNo = '';
	/**
	 * The status of the order
	 *
	 * @var OrderStatus
	 */
	protected $status;
	/**
	 * The payments that has been done for this order
	 *
	 * @var multiple:Payment
	 */
	protected $payments;
	/**
	 * The total amount due for the order
	 *
	 * @var number
	 */
	private $totalAmount = 0;
	/**
	 * The total amount paid for the order
	 *
	 * @var number
	 */
	private $totalPaid = 0;
	/**
	 * The shippment of the order
	 *
	 * @var multiple:Shippment
	 */
	protected $shippments;
	/**
	 * The shipping address
	 *
	 * @var Address
	 */
	protected $shippingAddr = null;
	/**
	 * The billing address
	 *
	 * @var Address
	 */
	protected $billingAddr = null;
	/**
	 * The array of order items
	 *
	 * @var Multiple:OrderItem
	 */
	protected $orderItems;
	/**
	 * The customer of this order
	 *
	 * @var Customer
	 */
	protected $customer;
	/**
	 * Wether the order passed the payment check
	 *
	 * @var bool
	 */
	private $passPaymentCheck;
	/**
	 * Whether this order is imported from B2B
	 *
	 * @var bool
	 */
	private $isFromB2B;
	/**
	 * The date and time when this order becomes an invoice
	 *
	 * @var UDate
	 */
	private $invDate;
	/**
	 * The Purchase Order No for the customer
	 *
	 * @var string
	 */
	private $pONo = '';
	/**
	 * The margin of each sale item
	 *
	 * @var item
	 */
	private $margin = 0;
	/**
	 * Getter for orderNo
	 *
	 * @return string
	 */
	public function getOrderNo()
	{
	    return $this->orderNo;
	}
	/**
	 * Setter for orderNo
	 *
	 * @param string $value The orderNo
	 *
	 * @return Order
	 */
	public function setOrderNo($value)
	{
	    $this->orderNo = $value;
	    return $this;
	}
	/**
	 * Getter for orderDate
	 *
	 * @return UDate
	 */
	public function getOrderDate()
	{
		if(is_string($this->orderDate))
			$this->orderDate = new UDate($this->orderDate);
	    return $this->orderDate;
	}
	/**
	 * Setter for orderDate
	 *
	 * @param string $value The orderDate
	 *
	 * @return Order
	 */
	public function setOrderDate($value)
	{
	    $this->orderDate = $value;
	    return $this;
	}

	/**
	 * Getter for type
	 *
	 * @return string
	 */
	public function getType()
	{
	    return $this->type;
	}
	/**
	 * Setter for type
	 *
	 * @param string $value The type
	 *
	 * @return Order
	 */
	public function setType($value)
	{
	    $this->type = $value;
	    return $this;
	}
	/**
	 * Getter for invNo
	 *
	 * @return string
	 */
	public function getInvNo()
	{
	    return $this->invNo;
	}
	/**
	 * Setter for invNo
	 *
	 * @param string $value The invNo
	 *
	 * @return Order
	 */
	public function setInvNo($value)
	{
	    $this->invNo = $value;
	    return $this;
	}
	/**
	 * Getter for status
	 *
	 * @return OrderStatus
	 */
	public function getStatus()
	{
		$this->loadManyToOne('status');
	    return $this->status;
	}
	/**
	 * Setter for status
	 *
	 * @param OrderStatus $value The status
	 *
	 * @return Order
	 */
	public function setStatus($value)
	{
	    $this->status = $value;
	    return $this;
	}
	/**
	 * Getter for payments
	 *
	 * @return Multiple:Payment
	 */
	public function getPayments()
	{
		$this->loadOneToMany('payments');
	    return $this->payments;
	}
	/**
	 * Setter for payments
	 *
	 * @param Multiple:Payment $value The payments
	 *
	 * @return Order
	 */
	public function setPayments($value)
	{
	    $this->payments = $value;
	    return $this;
	}
	/**
	 * Getter for totalAmount
	 *
	 * @return number
	 */
	public function getTotalAmount()
	{
	    return $this->totalAmount;
	}
	/**
	 * Setter for totalAmount
	 *
	 * @param number $value The totalAmount
	 *
	 * @return Order
	 */
	public function setTotalAmount($value)
	{
	    $this->totalAmount = $value;
	    return $this;
	}
	/**
	 * Getter for totalPaid
	 *
	 * @return number
	 */
	public function getTotalPaid()
	{
	    return $this->totalPaid;
	}
	/**
	 * Setter for totalPaid
	 *
	 * @param number $value The totalPaid
	 *
	 * @return Order
	 */
	public function setTotalPaid($value)
	{
	    $this->totalPaid = $value;
	    return $this;
	}
	/**
	 * Getter for shippments
	 *
	 * @return Shippment
	 */
	public function getShippments()
	{
		$this->loadOneToMany('shippments');
	    return $this->shippments;
	}
	/**
	 * Setter for shippments
	 *
	 * @param Shippment $value The shippments
	 *
	 * @return Order
	 */
	public function setShippments($value)
	{
	    $this->shippments = $value;
	    return $this;
	}
	/**
	 * Getter for the totalDue
	 *
	 * @return number
	 */
	public function getTotalDue()
	{
		return round($this->getTotalAmount() - $this->getTotalPaid(), 4);
	}
	/**
	 * Getter for shippingAddr
	 *
	 * @return Address
	 */
	public function getShippingAddr()
	{
		$this->loadManyToOne('shippingAddr');
	    return $this->shippingAddr;
	}
	/**
	 * Setter for shippingAddr
	 *
	 * @param Address $value The shippingAddr
	 *
	 * @return Order
	 */
	public function setShippingAddr(Address $value = null)
	{
	    $this->shippingAddr = $value;
	    return $this;
	}
	/**
	 * Getter for billingAddr
	 *
	 * @return Address
	 */
	public function getBillingAddr()
	{
		$this->loadManyToOne('billingAddr');
	    return $this->billingAddr;
	}
	/**
	 * Setter for billingAddr
	 *
	 * @param Address $value The billingAddr
	 *
	 * @return Order
	 */
	public function setBillingAddr(Address $value = null)
	{
	    $this->billingAddr = $value;
	    return $this;
	}
	/**
	 * Getter for passPaymentCheck
	 *
	 * @return bool
	 */
	public function getPassPaymentCheck()
	{
	    return trim($this->passPaymentCheck) === '1';
	}
	/**
	 * Setter for passPaymentCheck
	 *
	 * @param bool $value The passPaymentCheck
	 *
	 * @return Order
	 */
	public function setPassPaymentCheck($value)
	{
	    $this->passPaymentCheck = $value;
	    return $this;
	}
	/**
	 * Getter for orderItems
	 *
	 * @return Multiple:OrderItem
	 */
	public function getOrderItems()
	{
		$this->loadOneToMany('orderItems');
	    return $this->orderItems;
	}
	/**
	 * Setter for orderItems
	 *
	 * @param array $value The orderItems
	 *
	 * @return Order
	 */
	public function setOrderItems($value)
	{
		$this->orderItems = $value;
		return $this;
	}
	/**
	 * Getter for isFromB2B
	 *
	 * @return bool
	 */
	public function getIsFromB2B()
	{
	    return (trim($this->isFromB2B) === '1');
	}
	/**
	 * Setter for isFromB2B
	 *
	 * @param unkown $value The isFromB2B
	 *
	 * @return Order
	 */
	public function setIsFromB2B($value)
	{
	    $this->isFromB2B = $value;
	    return $this;
	}
	/**
	 * Getter for customer
	 *
	 * @return Customer
	 */
	public function getCustomer()
	{
		$this->loadManyToOne('customer');
	    return $this->customer;
	}
	/**
	 * Setter for customer
	 *
	 * @param unkown $value The customer
	 *
	 * @return Order
	 */
	public function setCustomer($value)
	{
	    $this->customer = $value;
	    return $this;
	}
	/**
	 * checking whether the order can be edit by a role
	 *
	 * @param Role $role The role who is trying to edit the roder
	 *
	 * @return boolean
	 */
	public function canEditBy(Role $role)
	{
		return AccessControl::canEditOrder($this, $role);
	}
	/**
	 * Getter for invDate
	 *
	 * @return invDate
	 */
	public function getInvDate()
	{
		if(is_string($this->invDate))
			$this->invDate = new UDate($this->invDate);
		return $this->invDate;
	}
	/**
	 * Setter for the invDate
	 *
	 * @param mixed $value
	 *
	 * @return Order
	 */
	public function setInvDate($value)
	{
		$this->invDate = $value;
		return $this;
	}
	/**
	 * Getter for pONo
	 *
	 * @return
	 */
	public function getPONo()
	{
	    return $this->pONo;
	}
	/**
	 * Setter for pONo
	 *
	 * @param unkown $value The pONo
	 *
	 * @return Order
	 */
	public function setPONo($value)
	{
	    $this->pONo = $value;
	    return $this;
	}
	/**
	 * Adding a payment to this order
	 *
	 * @param PaymentMethod $method
	 * @param double        $value
	 * @param string        $comments
	 *
	 * @return Order
	 */
	public function addPayment(PaymentMethod $method, $value, $comments = '')
	{
		return Payment::create($this, $method, $value, $comments)->getOrder();
	}
	/**
	 * Getter for margin
	 *
	 * @return double
	 */
	public function getMargin()
	{
		return $this->margin;
	}
	/**
	 * Setter for margin
	 *
	 * @param int $value The margin
	 *
	 * @return OrderItem
	 */
	public function setMargin($value)
	{
		$this->margin = $value;
		return $this;
	}
	/**
	 * Getting the order's previous status
	 *
	 * @return Ambigous <Ambigous, BaseEntityAbstract, NULL, SimpleXMLElement>
	 */
	public function getPreviousStatus()
	{
		$prevouseStatusId = $this->getInfo(OrderInfoType::ID_MAGE_ORDER_STATUS_BEFORE_CHANGE, true);
		if(count($prevouseStatusId) > 0)
			return OrderStatus::get($prevouseStatusId[0]);
		return null;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getInvDate()) === '')
			$this->setInvDate(Udate::zeroDate());
		if(trim($this->getId()) !== '')
		{
			$this->setMargin($this->getCalculatedTotalMargin());
			//status changed
			$originalOrder = self::get($this->getId());
			if($originalOrder instanceof Order && $originalOrder->getStatus()->getId() !== $this->getStatus()->getId())
			{
				$infoType = OrderInfoType::get(OrderInfoType::ID_MAGE_ORDER_STATUS_BEFORE_CHANGE);
				$orderInfos = OrderInfo::find($this, $infoType, false, 1, 1);
				$orderInfo = count($orderInfos) === 0 ? null : $orderInfos[0];
				OrderInfo::create($this, $infoType, $originalOrder->getStatus()->getId(), $orderInfo);
				$this->addLog('Changed Status from [' . $originalOrder->getStatus()->getName() . '] to [' . $this->getStatus() .']', Log::TYPE_SYSTEM, 'Auto Log', get_class($this) . '::' . __FUNCTION__);
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if(trim($this->getOrderNo()) === '')
		{
			$this->setOrderNo('BPCM' .str_pad($this->getId(), 8, '0', STR_PAD_LEFT))
				->setMargin($this->getCalculatedTotalMargin())
				->save();
		}
		if(trim($this->getType()) === trim(self::TYPE_INVOICE))
			$this->_changeToInvoice();

		//if the order is now SHIPPED
		if(trim($this->getStatus()->getId()) === trim(OrderStatus::ID_SHIPPED)) {
			$items = OrderItem::getAllByCriteria('orderId = ? and isPicked = 1', array($this->getId()));
			foreach($items as $item) {
				$item->setIsShipped(true)
					->save();
			}
			$this->_changeToInvoice();
		}
	}
	/**
	 * calculate total margin for an order
	 *
	 * @return number
	 */
	public function getCalculatedTotalMargin()
	{
		$totalMargin = 0;
		foreach($this->getOrderItems() as $item)
			$totalMargin += $item->getMargin();
		return $totalMargin;
	}
	/**
	 * changed the order to be a invoice
	 *
	 * @return Ambigous <BaseEntityAbstract, GenericDAO>
	 */
	private function _changeToInvoice()
	{
		if(trim($this->getInvNo()) !== "")
			return $this;
		return $this->setType(Order::TYPE_INVOICE)
			->setInvNo('BPCINV' .str_pad($this->getId(), 8, '0', STR_PAD_LEFT))
			->setInvDate(new UDate())
			->save()
			->addComment('Changed this order to be an INVOCE with invoice no: ' . $this->getInvNo(), Comments::TYPE_SYSTEM, 'Auto Log', __CLASS__ . '::' . __FUNCTION__)
			->addLog('Changed this order to be an INVOCE with invoice no: ' . $this->getInvNo(), Log::TYPE_SYSTEM, 'Auto Log', __CLASS__ . '::' . __FUNCTION__);
	}
	/**
	 * adding a item onto the order
	 *
	 * @param Product $product
	 * @param number  $unitPrice
	 * @param number  $qty
	 * @param number  $totalPrice
	 * @param number  $mageOrderItemId The order_item_id from Magento
	 * @param string  $eta
	 *
	 * @return PurchaseOrder
	 */
	public function addItem(Product $product, $unitPrice = '0.0000', $qty = 1, $description = '', $totalPrice = null, $mageOrderItemId = null, $eta = null, $itemDescription= '')
	{
		OrderItem::create($this, $product, $unitPrice, $qty, $totalPrice, $mageOrderItemId, $eta, $itemDescription);
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = array(), $reset = false)
	{
		$array = $extra;
	    if(!$this->isJsonLoaded($reset))
	    {
	    	$array['customer'] = $this->getCustomer()->getJson();
	    	$array['totalDue'] = $this->getTotalDue();
	    	$array['infos'] = array();
	    	$array['address']['shipping'] = $this->getShippingAddr() instanceof Address ? $this->getShippingAddr()->getJson() : array();
	    	$array['address']['billing'] = $this->getBillingAddr() instanceof Address ? $this->getBillingAddr()->getJson() : array();
		    foreach($this->getInfos() as $info)
		    {
		    	if(!$info instanceof OrderInfo)
		    		continue;
		        $typeId = $info->getType()->getId();
		        if(!isset($array['infos'][$typeId]))
		            $array['infos'][$typeId] = array();
	            $array['infos'][$typeId][] = $info->getJson();
		    }
		    $array['status'] = $this->getStatus() instanceof OrderStatus ? $this->getStatus()->getJson() : array();

		    $array['shippments'] = array();
		    foreach($this->getShippments() as $shippment)
		    {
		    	if(!$shippment instanceof Shippment)
		    		continue;
		    	$array['shippments'][] = $shippment->getJson();
		    }
	    }
	    return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'ord');
		DaoMap::setStringType('orderNo');
		DaoMap::setStringType('type', 'varchar', 10);
		DaoMap::setStringType('invNo');
		DaoMap::setDateType('invDate');
		DaoMap::setDateType('orderDate');
		DaoMap::setManyToOne('customer', 'Customer', 'o_cust');
		DaoMap::setIntType('totalAmount', 'Double', '10,4');
		DaoMap::setIntType('totalPaid', 'Double', '10,4');
		DaoMap::setBoolType('passPaymentCheck');
		DaoMap::setBoolType('isFromB2B');
		DaoMap::setManyToOne('status', 'OrderStatus', 'o_status');
		DaoMap::setManyToOne('billingAddr', 'Address', 'baddr', true);
		DaoMap::setManyToOne('shippingAddr', 'Address', 'saddr', true);
		DaoMap::setStringType('pONo', 'varchar', 50);
		DaoMap::setIntType('margin', 'Double', '10,4');

		DaoMap::setOneToMany('shippments', 'Shippment', 'o_ship');
		DaoMap::setOneToMany('payments', 'Payment', 'py');
		DaoMap::setOneToMany('orderItems', 'OrderItem', 'o_items');
		parent::__loadDaoMap();

		DaoMap::createUniqueIndex('orderNo');
		DaoMap::createIndex('invNo');
		DaoMap::createIndex('invDate');
		DaoMap::createIndex('type');
		DaoMap::createIndex('orderDate');
		DaoMap::createIndex('passPaymentCheck');
		DaoMap::createIndex('isFromB2B');
		DaoMap::createIndex('pONo');
		DaoMap::createIndex('margin');
		DaoMap::createIndex('totalAmount');
		DaoMap::createIndex('totalPaid');
		DaoMap::commit();
	}
	/**
	 * Getting the order by order no
	 *
	 * @param string $orderNo
	 *
	 * @return Ambigous <NULL, unknown>
	 */
	public static function getByOrderNo($orderNo)
	{
		$items = self::getAllByCriteria('orderNo = ?', array($orderNo), false, 1, 1);
		return (count($items) === 0 ? null : $items[0]);
	}
	/**
	 *
	 * @param Customer    $customer
	 * @param string      $orderNo
	 * @param OrderStatus $status
	 * @param string      $orderDate
	 * @param string      $type
	 * @param string      $isFromB2B
	 * @param Address     $shipAddr
	 * @param Address     $billAddr
	 */
	public static function create(Customer $customer, $type = self::TYPE_ORDER, $orderNo = null, $comments = '', OrderStatus $status = null, $orderDate = null, $isFromB2B = false, Address $shipAddr = null, Address $billAddr = null, $passPaymentCheck = false, $poNo = '', Order $cloneFrom = null)
	{
		$order = new Order();
		$order->setOrderNo(trim($orderNo))
			->setCustomer($customer)
			->setStatus($status instanceof OrderStatus ? $status : OrderStatus::get(OrderStatus::ID_NEW))
			->setOrderDate(trim($orderDate) === '' ? trim(new UDate()) : trim($orderDate))
			->setIsFromB2B($isFromB2B)
			->setType(trim($type) === '' ? self::TYPE_ORDER : trim($type))
			->setShippingAddr($shipAddr instanceof Address ? $shipAddr : $customer->getShippingAddress())
			->setBillingAddr($billAddr instanceof Address ? $billAddr : $customer->getBillingAddress())
			->setPassPaymentCheck($passPaymentCheck)
			->setPONo(trim($poNo));
		if($cloneFrom instanceof Order) {
			$counts = intval(OrderInfo::countByCriteria('orderId = ? and typeId = ?', array($cloneFrom->getId(), OrderInfoType::ID_CLONED_FROM_ORDER_NO)));
			$newOrderNo = $cloneFrom->getOrderNo() . '-' . ($counts + 1);
			$order->setOrderNo($newOrderNo);
			$cloneFrom->addComment(($msg = 'A new order has been clone from this order:' . $newOrderNo), Comments::TYPE_SYSTEM)
				->addLog($msg, Log::TYPE_SYSTEM);
		}
		$order->save();
		if($cloneFrom instanceof Order) {
			$order->addInfo(OrderInfoType::ID_CLONED_FROM_ORDER_NO, $cloneFrom->getOrderNo(), true)
				->addComment(($msg = 'Cloned from Order:' . $cloneFrom->getOrderNo()), Comments::TYPE_SYSTEM)
				->addLog($msg, Comments::TYPE_SYSTEM);
		}
		$order->addComment($comments, Comments::TYPE_NORMAL)
			->addInfo(OrderInfoType::ID_CUS_EMAIL, $customer->getEmail())
			->addInfo(OrderInfoType::ID_CUS_NAME, $customer->getName())
			->addLog('Order (OrderNo.=' . $order->getOrderNo() . ') created with status: ' . $order->getStatus()->getName(), Log::TYPE_SYSTEM, 'Auto Log', __CLASS__ . '::' . __FUNCTION__);
		return $order;
	}
	/**
	 * Getting all the types of an order
	 *
	 * @return multitype:string
	 */
	public static function getAllTypes()
	{
		return array(self::TYPE_QUOTE, self::TYPE_ORDER, self::TYPE_INVOICE);
	}
}