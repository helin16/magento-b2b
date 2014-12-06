<?php
/** Location Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Location extends BaseEntityAbstract
{
	/**
	 * The name of the Location
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * The Description of the type
	 * 
	 * @var string
	 */
	private $description = '';
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
	 * @return Location
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return 
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
	 * @return LocationCodeType
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * Creating the Location based on sku
	 * 
	 * @param string $sku           The sku of the Location
	 * @param string $name          The name of the Location
	 * 
	 * @return Ambigous <Location, Ambigous, NULL, BaseEntityAbstract>
	 */
	public static function create($name, $description = '')
	{
		$class = __CLASS__;
		$obj = new $class();
		$obj->setName(trim($name))
			->setDescription(trim($description))
			->save();
		return $obj;
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
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'location');
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('name');
		DaoMap::createIndex('description');
		DaoMap::commit();
	}
}