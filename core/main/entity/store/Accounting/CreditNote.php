<?php
class CreditNote extends BaseEntityAbstract
{
	const APPLY_TO_REFUND = 'REFUND';
	const APPLY_TO_CREDIT = 'CREDIT';
	/**
	 * The creditNote items
	 *
	 * @var array
	 */
	protected $items;
	/**
	 * The creditNote No
	 *
	 * @var string
	 */
	private $creditNoteNo = '';
	/**
	 * The customer
	 *
	 * @var Customer
	 */
	protected $customer;
	/**
	 * Order this creditnot is for
	 *
	 * @var Order
	 */
	protected $order = null;
	/**
	 * The creditnote applyTo
	 *
	 * @var string
	 */
	private $applyTo = '';
	/**
	 * Applying Date
	 *
	 * @var UDate
	 */
	private $applyDate;
	/**
	 * Total value of the creditNote
	 *
	 * @var double
	 */
	private $totalValue = 0.0000;
	/**
	 * Total paid amount
	 * 
	 * @var double
	 */
	private $totalPaid = 0.0000;
	/**
	 * shipping value of the creditnote
	 * 
	 * @var double
	 */
	private $shippingValue = 0.0000;
	/**
	 * The description
	 *
	 * @var string
	 */
	private $description = '';
	/**
	 * getter for items
	 *
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}
	/**
	 * Setter for items
	 *
	 * @return CreditNote
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}
	/**
	 * Getter for order
	 *
	 * @return Order|NULL
	 */
	public function getOrder()
	{
		$this->loadManyToOne('order');
	    return $this->order;
	}
	/**
	 * Setter for order
	 *
	 * @param Order $value The order
	 *
	 * @return CreditNote
	 */
	public function setOrder($value)
	{
	    $this->order = $value;
	    return $this;
	}
	/**
	 * Getter for applyTo
	 *
	 * @return string
	 */
	public function getApplyTo()
	{
	    return $this->applyTo;
	}
	/**
	 * Setter for applyTo
	 *
	 * @param unkown $value The applyTo
	 *
	 * @return CreditNote
	 */
	public function setApplyTo($value)
	{
	    $this->applyTo = $value;
	    return $this;
	}
	/**
	 * Getter for totalValue
	 *
	 * @return double
	 */
	public function getTotalValue()
	{
	    return $this->totalValue;
	}
	/**
	 * Setter for totalValue
	 *
	 * @param double $value The totalValue
	 *
	 * @return CreditNote
	 */
	public function setTotalValue($value)
	{
	    $this->totalValue = $value;
	    return $this;
	}
	/**
	 * Getter for totalPaid
	 *
	 * @return double
	 */
	public function getTotalPaid()
	{
	    return $this->totalPaid;
	}
	/**
	 * Setter for totalPaid
	 *
	 * @param double $value The totalPaid
	 *
	 * @return CreditNote
	 */
	public function setTotalPaid($value)
	{
	    $this->totalPaid = $value;
	    return $this;
	}
	/**
	 * Getter for applyDate
	 *
	 * @return UDate
	 */
	public function getApplyDate()
	{
	    return new UDate(trim($this->applyDate));
	}
	/**
	 * Setter for applyDate
	 *
	 * @param mixed $value The applyDate
	 *
	 * @return CreditNote
	 */
	public function setApplyDate($value)
	{
	    $this->applyDate = $value;
	    return $this;
	}
	/**
	 * Getter for creditNoteNo
	 *
	 * @return string
	 */
	public function getCreditNoteNo()
	{
	    return $this->creditNoteNo;
	}
	/**
	 * Setter for creditNoteNo
	 *
	 * @param unkown $value The creditNoteNo
	 *
	 * @return CreditNote
	 */
	public function setCreditNoteNo($value)
	{
	    $this->creditNoteNo = $value;
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
	 * @param Customer $value The customer
	 *
	 * @return CreditNote
	 */
	public function setCustomer($value)
	{
	    $this->customer = $value;
	    return $this;
	}
	/**
	 * The getter for description
	 *
	 * @return string
	 */
	public function getDescription ()
	{
	    return $this->description;
	}
	/**
	 * Setter for description
	 *
	 * @param mixed $value The new value of description
	 *
	 * @return CreditNote
	 */
	public function setDescription ($value)
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * The getter for shippingValue
	 *
	 * @return double
	 */
	public function getShippingValue ()
	{
	    return $this->shippingValue;
	}
	/**
	 * Setter for shippingValue
	 * 
	 * @param mixed $value The new value of shippingValue
	 *
	 * @return CreditNote
	 */
	public function setShippingValue ($value)
	{
	    $this->shippingValue = $value;
	    return $this;
	}
	/**
	 * Adding a payment to this creditNote
	 *
	 * @param PaymentMethod $method
	 * @param double        $value
	 * @param string        $comments
	 *
	 * @return Order
	 */
	public function addPayment(PaymentMethod $method, $value, $comments = '', $paymentDate = null, &$newPayment = null)
	{
		$newPayment = Payment::createFromCreditNote($this, $method, $value, $comments, $paymentDate);
		return $newPayment->getOrder();
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getId()) !== '') {
			$items = CreditNoteItem::getAllByCriteria('creditNoteId = ?', array($this->getId()));
			$total = 0;
			foreach($items as $item)
				$total += $item->getQty() * $item->getUnitPrice();
			$this->setTotalValue($total);
			
			$payments = Payment::getAllByCriteria('creditNoteId = ?', array($this->getId()));
			$totalPaid = 0;
			foreach($payments as $payment)
				$totalPaid += $payment->getValue();
			$this->setTotalPaid($totalPaid);
		}
		$this->setTotalValue($this->getTotalValue() + $this->getShippingValue());
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if(trim($this->getCreditNoteNo()) === '') {
			$this->setCreditNoteNo('BPCC' . str_pad($this->getId(), 8, '0', STR_PAD_LEFT))
				->save();
			if($this->getOrder() instanceof Order) {
				$msg = "An Credit Note(" . $this->getCreditNoteNo() . ") has created for this order with a unitPrice: " . StringUtilsAbstract::getCurrency($this->getUnitPrice()) . ', qty: ' . $this->getQty() . ', totalValue: ' . StringUtilsAbstract::getCurrency($this->getTotalValue());
				$this->getOrder()
					->addComment($msg, Comments::TYPE_SYSTEM)
					->addLog($msg, Log::TYPE_SYSTEM, 'AUTO', __CLASS__ . '::' . __FUNCTION__);
			}
		}
		if($this->order instanceof Order)
		{
			$totalCreditNoteValue = 0;
			foreach (CreditNote::getAllByCriteria('cn.orderId = ?', array($this->order->getId())) as $creditNote)
			{
				if($creditNote->getTotalValue() >= 0)
					$totalCreditNoteValue += $creditNote->getTotalValue();
				else throw new Exception('Credit Note (id=' . $creditNote->getId() . ') has a negative total value.');
				$this->order->setTotalCreditNoteValue($totalCreditNoteValue)->save();
			}
		}
	}
	/**
	 * Adding a creaditNoteItem to this creditNote
	 *
	 * @param Product $product
	 * @param int     $qty
	 * @param double  $unitPrice
	 * @param double  $itemDescription
	 * @param duble   $unitCost
	 *
	 * @return CreditNote
	 */
	public function addItem(Product $product, $qty, $unitPrice, $itemDescription = '', $unitCost = null, $totalPrice = null, &$creditNoteItem = null)
	{
		$creditNoteItem = CreditNoteItem::create($this, $product, $qty, $unitPrice, $itemDescription, $unitCost, $totalPrice);
		return $this;
	}
	/**
	 * Adding credit Note item from a orderitem
	 *
	 * @param OrderItem $orderItem
	 * @param int       $qty
	 * @param double    $unitPrice
	 * @param double    $itemDescription
	 * @param duble     $unitCost
	 *
	 * @return CreditNote
	 */
	public function addItemFromOrderItem(OrderItem $orderItem, $qty, $unitPrice, $itemDescription = '', $unitCost = null, $totalPrice = null, &$creditNoteItem = null)
	{
		$creditNoteItem = CreditNoteItem::createFromOrderItem($this, $orderItem, $qty, $unitPrice, $itemDescription, $unitCost, $totalPrice);
		return $this;
	}
	/**
	 * Getting all the credit note items
	 *
	 * @return Ambigous <Ambigous, NULL, multitype:, multitype:BaseEntityAbstract >
	 */
	public function getCreditNoteItems()
	{
		return CreditNoteItem::getByCreditNote($this);
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
			$array['order'] = $this->getOrder() instanceof Order ? $this->order->getJson() : array();
		}
		return parent::getJson($array, $reset);
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'cn');

		DaoMap::setStringType('creditNoteNo', 'varchar', 12);
		DaoMap::setManyToOne('customer', 'Customer', 'cn_cus');
		DaoMap::setManyToOne('order', 'Order', 'cn_ord', true);
		DaoMap::setStringType('applyTo', 'varchar', 10);
		DaoMap::setDateType('applyDate');
		DaoMap::setIntType('totalValue', 'double', '10,4');
		DaoMap::setIntType('totalPaid', 'double', '10,4');
		DaoMap::setIntType('shippingValue', 'double', '10,4');
		DaoMap::setStringType('description', 'varchar', 255);
		DaoMap::setOneToMany('items', 'CreditNoteItem', 'cn_item');
		parent::__loadDaoMap();

		DaoMap::createUniqueIndex('creditNoteNo');
		DaoMap::createIndex('applyTo');
		DaoMap::createIndex('applyDate');

		DaoMap::commit();
	}
	/**
	 * Creating a CreditNote
	 *
	 * @param Customer $customer
	 *
	 * @return CreditNote
	 */
	public static function create(Customer $customer, $description = '')
	{
		$creditNote = new CreditNote();
		return $creditNote->setCustomer($customer)
			->setDescription(trim($description))
			->save();
	}
	/**
	 * Creating a credit Note from a order
	 *
	 * @param Order    $order
	 * @param Customer $customer
	 *
	 * @return CreditNote
	 */
	public static function createFromOrder(Order $order, Customer $customer = null, $description = '')
	{
		$creditNote = new CreditNote();
		$creditNote = $creditNote->setOrder($order)
			->setCustomer($customer instanceof Customer ? $customer : $order->getCustomer())
			->setDescription(trim($description))
			->save();
		$msg = 'An CreditNote(' . $creditNote->getCreditNoteNo() . ') has been created for Order(ID= ' . $order->getId() . ', OrderNo.=' . $order->getOrderNo() . '): ' . $description;
		$order->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Log::TYPE_SYSTEM);
		return $creditNote;
	}
	/**
	 * getting the types for the applyTo
	 *
	 * @return multitype:string
	 */
	public static function getApplyToTypes()
	{
		return array(self::APPLY_TO_CREDIT, self::APPLY_TO_REFUND);
	}
}