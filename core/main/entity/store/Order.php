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
	/**
	 * The order No from magento
	 * 
	 * @var string
	 */
	private $orderNo;
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
	private $invNo;
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
	private $totalAmount;
	/**
	 * The total amount paid for the order
	 *
	 * @var number
	 */
	private $totalPaid;
	/**
	 * The shippment of the order
	 * 
	 * @var multiple:Shippment
	 */
	protected $shippments;
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
	public function setinvNo($value) 
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
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'order');
		DaoMap::setStringType('orderNo');
		DaoMap::setStringType('invNo');
		DaoMap::setDateType('orderDate');
		DaoMap::setIntType('totalAmount', 'Double', '10,4');
		DaoMap::setIntType('totalPaid', 'Double', '10,4');
		DaoMap::setManyToOne('status', 'OrderStatus', 'o_status');
		
		DaoMap::setOneToMany('shippments', 'Shippment', 'o_ship');
		DaoMap::setOneToMany('payments', 'Payment', 'o_pay');
		parent::__loadDaoMap();
		
		DaoMap::createIndex('orderNo');
		DaoMap::createIndex('invNo');
		DaoMap::createIndex('orderDate');
		DaoMap::commit();
	}
}