<?php
class OrderStatus extends InfoEntityAbstract
{
	private $name;
	/**
	 * Getter for the name
	 */
	public function getName() 
	{
	    return $this->name;
	}
	/**
	 * Setter for the name
	 * 
	 * @param string $value The name of the status
	 * 
	 * @return OrderStatus
	 */
	public function setName($value) 
	{
	    $this->name = $value;
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
		DaoMap::begin($this, 'ost');
	
		DaoMap::setStringType('name', 'varchar', 32);
		parent::__loadDaoMap();
	
		DaoMap::createUniqueIndex('name');
		DaoMap::commit();
	}
}