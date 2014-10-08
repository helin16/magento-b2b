<?php
class InfoTypeAbstract extends BaseEntityAbstract
{
	/**
	 * The cache of the object
	 * 
	 * @var array
	 */
	protected static $_cache;
	/**
	 * The name of the type
	 * 
	 * @var string
	 */
	private $name;
	/**
	 * Getter for the name
	 * 
	 * @return string
	 */
	public function getName() 
	{
	    return $this->name;
	}
	/**
	 * Setter for the name
	 * 
	 * @param string $value The name of the type
	 * 
	 * @return InfoTypeAbstract
	 */
	public function setName($value) 
	{
	    $this->name = $value;
	    return $this;
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
			self::$_cache[$typeId] = self::get($typeId);
		}
		return self::$_cache[$typeId];
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::setStringType('name','varchar', 100);
		parent::__loadDaoMap();
		DaoMap::createIndex('name');
	}
}