<?php
class RMA extends BaseEntityAbstract
{
	const STATUS_NEW = 'NEW';
	const STATUS_RECEIVING = 'RECEIVING';
	const STATUS_RECEIVED = 'RECEIVING';
	const STATUS_CLOSED = 'CLOSED';
	/**
	 * The RA no
	 *
	 * @var string
	 */
	private $raNo = '';
	/**
	 * The order for this RA
	 *
	 * @var Order
	 */
	protected $order = null;
	/**
	 * The customer for this RA
	 *
	 * @var Customer
	 */
	protected $customer;
	/**
	 * The totalvalue of this RA
	 *
	 * @var double
	 */
	private $totalValue = 0;
	/**
	 * The description of this RA
	 *
	 * @var string
	 */
	private $description;
	/**
	 * The status
	 * 
	 * @var string
	 */
	private $status = self::STATUS_NEW;
	/**
	 * Getter for raNo
	 *
	 * @return string
	 */
	public function getRaNo()
	{
	    return $this->raNo;
	}
	/**
	 * Setter for raNo
	 *
	 * @param string $value The raNo
	 *
	 * @return RMA
	 */
	public function setRaNo($value)
	{
	    $this->raNo = $value;
	    return $this;
	}
	/**
	 * Getter for order
	 *
	 * @return Order
	 */
	public function getOrder()
	{
		$this->loadManyToOne('order');
	    return $this->order;
	}
	/**
	 * Setter for order
	 *
	 * @param unkown $value The order
	 *
	 * @return RMA
	 */
	public function setOrder(Order $value = null)
	{
	    $this->order = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return string
	 */
	public function getDescription()
	{
	    return $this->description;
	}
	/**
	 * Setter for description
	 *
	 * @param string $value The description
	 *
	 * @return RMA
	 */
	public function setDescription($value)
	{
	    $this->description = $value;
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
	 * @return RMA
	 */
	public function setCustomer(Customer $value)
	{
	    $this->customer = $value;
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
	 * @return RMA
	 */
	public function setTotalValue($value)
	{
	    $this->totalValue = $value;
	    return $this;
	}
	/**
	 * The getter for status
	 *
	 * @return string
	 */
	public function getStatus ()
	{
	    return $this->status;
	}
	/**
	 * Setter for status
	 * 
	 * @param mixed $value The new value of status
	 *
	 * @return RMA
	 */
	public function setStatus ($value)
	{
	    $this->status = $value;
	    return $this;
	}
	/**
	 * Adding a RMAItem to this RMA
	 *
	 * @param Product $product         The product we are raising RMA for
	 * @param int     $qty             The qty
	 * @param double  $itemDescription The item description
	 * @param duble   $unitCost        The unitcost of that the product
	 * @param mixed   $rmaItem         The new created RMAItem
	 *
	 * @return RMA
	 */
	public function addItem(Product $product, $qty, $itemDescription = '', $unitCost = null, &$rmaItem = null)
	{
		$rmaItem = RMAItem::create($this, $product, $qty, $itemDescription, $unitCost);
		return $this;
	}
	/**
	 * Adding RMAItem from a orderitem
	 *
	 * @param OrderItem $orderItem       The product we are raising RMA for
	 * @param int       $qty             The qty
	 * @param double    $itemDescription The item description
	 * @param duble     $unitCost        The unitcost of that the product
	 * @param mixed   $rmaItem           The new created RMAItem
	 *
	 * @return RMA
	 */
	public function addItemFromOrderItem(OrderItem $orderItem, $qty, $itemDescription = '', $unitCost = null, &$rmaItem = null)
	{
		$rmaItem = RMAItem::createFromOrderItem($this, $orderItem, $qty, $itemDescription, $unitCost);
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::preSave()
	 */
	public function preSave()
	{
		if(trim($this->getId()) !== '') {
			$items = RMAItem::getAllByCriteria('RMAId = ?', array($this->getId()));
			$total = 0;
			foreach($items as $item)
				$total += $item->getQty() * $item->getUnitCost();
			$this->setTotalValue($total);
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::postSave()
	 */
	public function postSave()
	{
		if(trim($this->getRaNo()) === '') {
			$this->setRaNo('BPCR' . str_pad($this->getId(), 8, '0', STR_PAD_LEFT))
				->save();
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'ra');

		DaoMap::setStringType('raNo', 'varchar', 12);
		DaoMap::setStringType('status', 'varchar', 12);
		DaoMap::setManyToOne('order', 'Order', 'ra_order', true);
		DaoMap::setManyToOne('customer', 'Customer', 'ra_customer');
		DaoMap::setIntType('totalValue', 'double', '10,4');
		DaoMap::setStringType('description', 'varchar', '255');

		parent::__loadDaoMap();

		DaoMap::createUniqueIndex('raNo');
		DaoMap::createIndex('status');
		DaoMap::createIndex('totalValue');

		DaoMap::commit();
	}
	/**
	 * Creating a RMA
	 *
	 * @param Customer $customer
	 *
	 * @return RMA
	 */
	public static function create(Customer $customer, $description = '')
	{
		$ra = new RMA();
		return $ra->setCustomer($customer)
			->setDescription(trim($description))
			->save();
	}
	/**
	 * Creating a RMA from a order
	 *
	 * @param Order    $order
	 * @param Customer $customer
	 *
	 * @return RMA
	 */
	public static function createFromOrder(Order $order, Customer $customer = null, $description = '')
	{
		$ra = new RMA();
		$ra = $ra->setOrder($order)
			->setCustomer($customer instanceof Customer ? $customer : $order->getCustomer())
			->setDescription(trim($description))
			->save();
		$msg = 'A RMA(' . $ra->getRaNo() . ') has been created for Order(ID= ' . $order->getId() . ', OrderNo.=' . $order->getOrderNo() . '): ' . $description;
		$order->addComment($msg, Comments::TYPE_SYSTEM)
			->addLog($msg, Log::TYPE_SYSTEM);
		return $ra;
	}
	/**
	 * Getting all the statuses for the RMA
	 * 
	 * @return multitype:string
	 */
	public static function getAllStatuses()
	{
		return array(self::STATUS_NEW, self::STATUS_RECEIVING, self::STATUS_RECEIVED, self::STATUS_CLOSED);
	}
}