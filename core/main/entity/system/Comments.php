<?php
/** Comments Entity
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Comments extends BaseEntityAbstract
{
	const TYPE_NORMAL = 'NORMAL';
	const TYPE_SYSTEM = 'SYSTEM';
	const TYPE_PURCHASING = 'PURCHASING';
	const TYPE_WAREHOUSE = 'WAREHOUSE';
	const TYPE_ACCOUNTING = 'ACCOUNTING';
	const TYPE_CUSTOMER = 'CUSTOMER';
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
	 * The type of the comments
	 * 
	 * @var string
	 */
	private $type;
	/**
	 * The groupId of a couple of comments
	 * 
	 * @var string
	 */
	private $groupId;
	/**
	 * The cached groupid
	 * 
	 * @var string
	 */
	private static $_groupId;
	/**
	 * Getting the transid
	 *
	 * @param string $salt The salt of making the trans id
	 *
	 * @return string
	 */
	public static function genGroupId($salt = '')
	{
		if(!is_string(self::$_groupId))
			self::$_groupId = StringUtilsAbstract::getRandKey($salt);
		return self::$_groupId;
	}
	/**
	 * add Comments
	 *
	 * @param BaseEntityAbstract $entity   The entity
	 * @param string             $comments The comemnts
	 * @param string             $groupId  The groupId
	 */
	public static function addComments(BaseEntityAbstract $entity, $comments = '', $type = self::TYPE_NORMAL, $groupId = '')
	{
		$className = __CLASS__;
		$en = new $className();
		return $en->setEntityId($entity->getId())
			->setEntityName(get_class($entity))
			->setComments($comments)
			->setType($type)
			->setGroupId(($groupId = trim($groupId)) === '' ? self::genGroupId() : $groupId)
			->save();
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
	 * Getter for type
	 *
	 * @return 
	 */
	public function getType() 
	{
	    return $this->type;
	}
	/**
	 * Setter for type
	 *
	 * @param unkown $value The type
	 *
	 * @return Comments
	 */
	public function setType($value) 
	{
	    $this->type = $value;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::getJson()
	 */
	public function getJson($extra = '', $reset = false)
	{
		$array = array();
	    if(!$this->isJsonLoaded($reset))
	    {
	    	$array['createdBy'] = array('id'=> $this->getCreatedBy()->getId(), 'person' => $this->getCreatedBy()->getPerson()->getJson());
	    	$array['updatedBy'] = array('id'=> $this->getUpdatedBy()->getId(), 'person' => $this->getUpdatedBy()->getPerson()->getJson());
	    }
	    return parent::getJson($array, $reset);
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
		DaoMap::setStringType('type','varchar', 50);
	
		parent::__loadDaoMap();
	
		DaoMap::createIndex('entityId');
		DaoMap::createIndex('entityName');
		DaoMap::createIndex('groupId');
		DaoMap::createIndex('type');
	
		DaoMap::commit();
	}
}