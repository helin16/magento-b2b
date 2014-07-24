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
	    $this->loadManyToOne('method');
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
		$this->loadManyToOne('order');
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
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = '', $reset = false)
	{
		$array = array();
		if(!$this->isJsonLoaded($reset))
		{
			$array['method'] = $this->getMethod()->getJson();
			$array['createdBy'] = $this->getCreatedBy()->getJson();
		}
		return parent::getJson($array, $reset);
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