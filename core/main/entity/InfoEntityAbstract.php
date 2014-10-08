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
	 * @param int $typeId
	 * @param string $reset
	 * @throws EntityException
	 */
	public function getInfo($typeId, $reset = false)
	{
		DaoMap::loadMap($this);
		if(!isset($this->_cache[$typeId]) || $reset === true)
		{
			if(!isset(DaoMap::$map[strtolower(get_class($this))]['infos']) || ($class = trim(DaoMap::$map[strtolower(get_class($this))]['infos']['class'])) === '')
				throw new EntityException('You can NOT get information from a entity' . get_class($this) . ', setup the relationship first!');
			
			$sql = 'select value from ' . strtolower($class) . ' `info` where `info`.active = 1 and `info`.' . strtolower(get_class($this)) . 'Id = ? and `info`.TypeId = ?';
			$result = Dao::getResultsNative($sql, array($this->getId(), $typeId), PDO::FETCH_NUM);
			$this->_cache[$typeId] = array_map(create_function('$row', 'return $row[0];'), $result);
		}
		return $this->_cache[$typeId];
	}
	/**
	 * adding new value to this entity
	 * 
	 * @param int  $typeId
	 * @param int  $value
	 * @param bool $overRideValue Whether we over write the value when we found one: clear all other value, and keep this new one
	 * 
	 * @return InfoEntityAbstract
	 */
	public function addInfo($typeId, $value, $overRideValue = false)
	{
		DaoMap::loadMap($this);
		if(!isset(DaoMap::$map[strtolower(get_class($this))]['infos']) || ($class = trim(DaoMap::$map[strtolower(get_class($this))]['infos']['class'])) === '')
			throw new EntityException('You can NOT get information from a entity' . get_class($this) . ', setup the relationship first!');
		
		$InfoTypeClass = $class . 'Type';
		$infoType = $InfoTypeClass::get($typeId);
		if($overRideValue === true)
		{
			//clear all info
			$this->removeInfo($typeId);
			//create a new
			$info = $class::create($this, $infoType, $value);
		}
		else 
		{
			//check whether we have one already
			$infos = self::getAllByCriteria(strtolower(get_class($this)).'Id = ? and value = ? and typeId = ?', array($this->getId(), trim($typeId), trim($value)), true, 1 , 1);
			if(count($infos) > 0)
				return $this;
			//create new
			$info = $class::create($this, $infoType, $value);
		}
		
		//referesh cache
		$this->getInfo($typeId, true);
		return $this;
	}
	/**
	 * removing all information for that type
	 * 
	 * @param int $typeId The type id 
	 * 
	 * @return InfoEntityAbstract
	 */
	public function removeInfo($typeId)
	{
		DaoMap::loadMap($this);
		if(!isset(DaoMap::$map[strtolower(get_class($this))]['infos']) || ($class = trim(DaoMap::$map[strtolower(get_class($this))]['infos']['class'])) === '')
			throw new EntityException('You can NOT get information from a entity' . get_class($this) . ', setup the relationship first!');
		
		self::updateByCriteria('active = 0', 'typeId = ? and entityId = ?', array($typeId, $this->getId()));
		unset($this->_cache[$typeId]);
		return $this;
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