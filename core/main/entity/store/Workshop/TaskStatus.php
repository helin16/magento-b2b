<?php
class TaskStatus extends BaseEntityAbstract
{
	const ID_NEW = 1;
	const ID_IN_PROGRESS = 2;
	const ID_FINISHED = 3;
	const ID_ON_HOLD = 4;
	const ID_CANCELED = 5;
	/**
	 * The name of the status;
	 *
	 * @var string
	 */
	private $name;
	/**
	 * The description of the status
	 *
	 * @var string
	 */
	private $description;
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
	 * @param unkown $value The name
	 *
	 * @return TaskStatus
	 */
	public function setName($value)
	{
	    $this->name = $value;
	    return $this;
	}
	/**
	 * Getter for description
	 *
	 * @return string
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
	 * @return TaskStatus
	 */
	public function setDescription($value)
	{
	    $this->description = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'tstat');

		DaoMap::setStringType('name','varchar', 20);
		DaoMap::setStringType('description','text');

		parent::__loadDaoMap();

		DaoMap::createUniqueIndex('name');
		DaoMap::commit();
	}
	/**
	 * Getting the task status from cache, or database if no cache found
	 *
	 * @param unknown $id
	 *
	 * @return TaskStatus
	 */
	public static function get($id)
	{
		if(self::cacheExsits($id))
			return self::getCache($id);
		if(!($taskStatus = parent::get($id)) instanceof TaskStatus)
			return null;
		self::addCache($id, $taskStatus);
		return $taskStatus;
	}
}