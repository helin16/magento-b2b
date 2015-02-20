<?php
class CreditNote extends BaseEntityAbstract
{
	const APPLY_TO_REFUND = 'REFUND';
	const APPLY_TO_CREDIT = 'CREDIT';
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
	private $totalValue = '';
	/**
	 * The description
	 * 
	 * @var string
	 */
	private $description = '';
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
		}
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
	public function addItem(Product $product, $qty, $unitPrice, $itemDescription = '', $unitCost = null, &$creditNoteItem = null)
	{
		$creditNoteItem = CreditNoteItem::create($this, $product, $qty, $unitPrice, $itemDescription, $unitCost);
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
	public function addItemFromOrderItem(OrderItem $orderItem, $qty, $unitPrice, $itemDescription = '', $unitCost = null, &$creditNoteItem = null)
	{
		$creditNoteItem = CreditNoteItem::createFromOrderItem($this, $orderItem, $qty, $unitPrice, $itemDescription, $unitCost);
		return $this;
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
		DaoMap::setStringType('description', 'varchar', 255);
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