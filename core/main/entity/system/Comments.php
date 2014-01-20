<?php
class Comments extends BaseEntityAbstract
{
	/**
	 * The id of the entity
	 * 
	 * @var int
	 */
	private $entityId;
	/**
	 * The name of the entity
	 * 
	 * @var string
	 */
	private $entityName;
	/**
	 * The comments
	 * 
	 * @var string
	 */
	private $comments;
	/**
	 * The groupId of a couple of comments
	 * 
	 * @var string
	 */
	private $groupId;
	/**
	 * Getting the transid
	 *
	 * @param string $salt The salt of making the trans id
	 *
	 * @return string
	 */
	public static function genGroupId($salt = '')
	{
		return StringUtilsAbstract::getRandKey($salt);
	}
	/**
	 * add Comments
	 *
	 * @param BaseEntityAbstract $entity   The entity
	 * @param string             $comments The comemnts
	 * @param string             $groupId  The groupId
	 */
	public static function addComments(BaseEntityAbstract $entity, $comments = '', $groupId = '')
	{
		$className = __CLASS__;
		$en = new $className();
		$en->setEntityId($entity->getId());
		$en->setEntityName(get_class($entity));
		$en->setComments($comments);
		$en->setGroupId($groupId);
		EntityDao::getInstance($className)->save($en);
	}
	/**
	 * Getter for EntityId
	 *
	 * @return int
	 */
	public function getEntityId() 
	{
	    return $this->entityId;
	}
	/**
	 * Setter for entity
	 *
	 * @param int $value The entity id
	 *
	 * @return Comments
	 */
	public function setEntityId($value) 
	{
	    $this->entityId = $value;
	    return $this;
	}
	/**
	 * Getter for entityName
	 *
	 * @return string
	 */
	public function getEntityName() 
	{
	    return $this->entityName;
	}
	/**
	 * Setter for entityName
	 *
	 * @param string $value The entityName
	 *
	 * @return Comments
	 */
	public function setEntityName($value) 
	{
	    $this->entityName = $value;
	    return $this;
	}
	/**
	 * Getter for comments
	 *
	 * @return string
	 */
	public function getComments() 
	{
	    return $this->comments;
	}
	/**
	 * Setter for comments
	 *
	 * @param string $value The comments
	 *
	 * @return Comments
	 */
	public function setComments($value) 
	{
	    $this->comments = $value;
	    return $this;
	}
	/**
	 * Getter for groupId
	 *
	 * @return string
	 */
	public function getGroupId() 
	{
	    return $this->groupId;
	}
	/**
	 * Setter for groupId
	 *
	 * @param string $value The groupId
	 *
	 * @return Comments
	 */
	public function setGroupId($value) 
	{
	    $this->groupId = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'comm');
	
		DaoMap::setIntType('entityId');
		DaoMap::setStringType('entityName','varchar', 100);
		DaoMap::setStringType('comments','varchar', 255);
		DaoMap::setStringType('groupId','varchar', 100);
	
		parent::__loadDaoMap();
	
		DaoMap::createIndex('entityId');
		DaoMap::createIndex('entityName');
		DaoMap::createIndex('groupId');
	
		DaoMap::commit();
	}
}