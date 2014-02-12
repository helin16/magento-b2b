<?php
class Payment extends BaseEntityAbstract
{
	protected $method;
	protected $order;
	private $value;
	/**
	 * Getter for method
	 *
	 * @return PaymentMethod
	 */
	public function getMethod() 
	{
	    return $this->method;
	}
	/**
	 * Setter for method
	 *
	 * @param PaymentMethod $value The method
	 *
	 * @return Payment
	 */
	public function setMethod(PaymentMethod $value) 
	{
	    $this->method = $value;
	    return $this;
	}
	/**
	 * Getter for order
	 *
	 * @return Order
	 */
	public function getOrder() 
	{
	    return $this->order;
	}
	/**
	 * Setter for order
	 *
	 * @param unkown $value The order
	 *
	 * @return Payment
	 */
	public function setOrder($value) 
	{
	    $this->order = $value;
	    return $this;
	}
	/**
	 * Getter for value
	 *
	 * @return number
	 */
	public function getValue() 
	{
	    return $this->value;
	}
	/**
	 * Setter for value
	 *
	 * @param number $value The value
	 *
	 * @return Payment
	 */
	public function setValue($value) 
	{
	    $this->value = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getMethod() . ': ' . $this->getValue() . " for Order:" . $this->getOrder()->getOrderNo());
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'py');
	
		DaoMap::setManyToOne('order', 'Order', 'ord');
		DaoMap::setManyToOne('method', 'PaymentMethod', 'py_method');
		DaoMap::setIntType('value', 'Double', '10,4');
		parent::__loadDaoMap();
	
		DaoMap::commit();
	}
}