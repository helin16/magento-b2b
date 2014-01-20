<?php
class InfoEntityAbstract extends BaseEntityAbstract
{
	/**
	 * The cache for info
	 * 
	 * @var array
	 */
	protected $_cache;
	/**
	 * The array of information
	 * 
	 * @var multiple:InfoAbstract
	 */
	protected $infos;
	/**
	 * Getting all the information
	 * 
	 * @return array
	 */
	public function getInfos() 
	{
		$this->loadOneToMany('infos');
	    return $this->infos;
	}
	/**
	 * Setter for the information
	 * 
	 * @param array $value The array of InfoAbstract
	 * 
	 * @return InfoEntityAbstract
	 */
	public function setInfos($value) 
	{
	    $this->infos = $value;
	    return $this;
	}
	/**
	 * Getting the 
	 * @param unknown $typeId
	 * @param string $reset
	 * @throws EntityException
	 */
	public function getInfo($typeId, $reset = false)
	{
		DaoMap::loadMap($this);
		if(!isset($this->_cache[$typeId]) || $reset === true)
		{
			if(!isset(DaoMap::$map[strtolower(get_class($this))]['infos']) || ($class = trim(aoMap::$map[strtolower(get_class($this))]['infos']['class'])) === '')
				throw new EntityException('You can NOT get information from a entity' . get_class($this) . ', setup the relationship first!');
			$sql = 'select lib.value `value` from ' . $class . ' info where info.active = 1 and info.entityId = ? and info.TypeId = ?';
			$result = Dao::getSingleResultNative($sql, array($separator, $this->getId(), $typeId), PDO::FETCH_ASSOC);
			$this->_cache[$typeCode] = array_map(create_function('$row', 'return $row["value"];'), $result);
		}
		return $this->_cache[$typeCode];
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::setOneToMany("infos", get_class($this) . "Info", strtolower(get_class($this)) . "_info");
		parent::__loadDaoMap();
	}
}