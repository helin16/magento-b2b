<?php
/**
 * Entity for Courier
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Courier extends BaseEntityAbstract
{
	/**
	 * The name of the courier
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The shippment of the courier
	 * 
	 * @var Multiple:Shippment
	 */
	protected $shippments;
	/**
	 * Getter for name
	 *
	 * @return string
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
	 * @return Courier
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for shippments
	 *
	 * @return Multiple:Shippment
	 */
	public function getShippments() 
	{
		$this->loadOneToMany('shippments');
	    return $this->shippments;
	}
	/**
	 * Setter for shippments
	 *
	 * @param array $value The shippments
	 *
	 * @return Courier
	 */
	public function setShippments(array $value) 
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
		DaoMap::begin($this, 'courier');
		DaoMap::setStringType('name');
		DaoMap::setOneToMany('shippments', 'Shippment', 'c_shippments');
		parent::__loadDaoMap();
		
		DaoMap::createIndex('name');
		DaoMap::commit();
	}
}