<?php
class PaymentMethod extends BaseEntityAbstract
{
	/**
	 * The name of the payment method
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The description of the payment method
	 * 
	 * @var string
	 */
	private $description;
	/**
	 * Getter for name
	 *
	 * @return PaymentMethod
	 */
	public function getName() 
	{
	    return $this->name;
	}
	/**
	 * Setter for name
	 *
	 * @param string $value The name
	 *
	 * @return PaymentMethod
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return PaymentMethod
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
	 * @return PaymentMethod
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
		return trim($this->getName());
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'py_method');
	
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('name');
		DaoMap::commit();
	}
}