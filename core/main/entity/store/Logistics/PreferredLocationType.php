<?php
/** PreferredLocationType Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class PreferredLocationType extends BaseEntityAbstract
{
	const ID_RECEIVED = 1;
	const ID_PICKED = 2;
	const ID_SHIPPED = 3;
	/**
	 * The cache of the object
	 *
	 * @var array
	 */
	protected static $_cache;
	/**
	 * The name of the PreferredLocationType
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
	 * @return PreferredLocationType
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
	 * @return PreferredLocationTypeCodeType
	 */
	public function setDescription($value) 
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * Creating the PreferredLocationType based on sku
	 * 
	 * @param string $sku           The sku of the PreferredLocationType
	 * @param string $name          The name of the PreferredLocationType
	 * 
	 * @return Ambigous <PreferredLocationType, Ambigous, NULL, BaseEntityAbstract>
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
		DaoMap::begin($this, 'pre_loc_type');
		DaoMap::setStringType('name', 'varchar', 100);
		DaoMap::setStringType('description', 'varchar', 255);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('name');
		DaoMap::createIndex('description');
		DaoMap::commit();
	}
	/**
	 * Getting object
	 *
	 * @param int $typeId The id of the type
	 *
	 * @return InfoTypeAbstract
	 */
	public static function get($typeId)
	{
		if(!isset(self::$_cache[$typeId]))
		{
			self::$_cache[$typeId] = parent::get($typeId);
		}
		return self::$_cache[$typeId];
	}
}