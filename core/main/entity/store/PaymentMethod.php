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
	 * The cache
	 * 
	 * @var array
	 */
	private static $_cache;
	/**
	 * Getting the PaymentMethod
	 * 
	 * @param int $id The id of the paymentmethod
	 * 
	 * @return PaymentMethod|null
	 */
	public static function get($id)
	{
		if(!isset(self::$_cache[$id]))
		{
			$entityClassName = trim(get_called_class());
			self::$_cache[$id] = FactoryAbastract::dao($entityClassName);
		}
		return self::$_cache[$id];
	}
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