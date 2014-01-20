<?php
/**
 * Entity for OrderStatus
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
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
	 * createStatus
	 * 
	 * @param string $status The name of the status
	 * 
	 * @return Ambigous <OrderStatus, BaseEntityAbstract>
	 */
	public static function createStatus($status)
	{
		$items = FactoryAbastract::dao(__CLASS__)->findByCriteria('name=?', array($status), false, 1, 1);
		$st = (count($items) === 0 ? new OrderStatus() : $items[0]);
		$st->setName($status);
		FactoryAbastract::dao(__CLASS__)->save($st);
		return $st;
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