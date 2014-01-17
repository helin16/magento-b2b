<?php
/**
 * Basic Abstract entity service
 * 
 * @package    Core
 * @subpackage Service
 * @author     lhe<helin16@gmail.com>
 */
abstract class BaseServiceAbastract
{
	/**
	 * @var EntityName
	 */
	protected $_entityName;
	/**
	 * The pagination stats
	 *
	 * @var array
	 */
	private $_pageStats = array();
	/**
	 * The registery of the services
	 *
	 * @var array
	 */
	private static $_instances = array();
	/**
	 * constructor
	 * 
	 * @param string $entityName The entity name of the service
	 */
	public function __construct($entityName)
	{
		$this->_entityName = $entityName;
		$this->_pageStats = Dao::getPageStats();
	}
	/**
	 * Singleton Design of the entity Dao
	 * 
	 * @param string $entityName The name of the entity
	 * 
	 * @return BaseServiceAbastract
	 */
	public static function getInstance($serviceName)
	{
		if(!array_key_exists($serviceName, self::$_instances))
		{
		    $className = $serviceName . 'Service';
		    if(!class_exists($className))
		        throw new ServiceException("$className does NOT exsits!");
		    self::$_instances[$serviceName] = new $className;
		}
		return self::$_instances[$serviceName];
	}
	/**
	 * Get an Entity By its Id
	 *
	 * @param int $id The id of the entity
	 * 
	 * @return BaseEntity
	 */
	public function get($id)
	{
		$orginal = DaoQuery::$selectActiveOnly;
		DaoQuery::$selectActiveOnly = false;
		$entity = EntityDao::getInstance($this->_entityName)->findById($id);
		DaoQuery::$selectActiveOnly = $orginal;
		return $entity;
	}
	/**
	 * Save an Entity
	 *
	 * @param Entity $entity The entity we are trying to save
	 * 
	 * @return BaseEntity
	 */
	public function save(BaseEntityAbstract $entity)
	{
	    EntityDao::getInstance($this->_entityName)->save($entity);
	    return $entity;
	}
	/**
	 * Finding all entries for that entity
	 * 
	 * @param bool  $searchActiveOnly Whether we will get the active one only
	 * @param int   $page             The page number of the pagination
	 * @param int   $pagesize         The page size of the pagination
	 * @param array $orderBy          The order by fields. i.e.: array("UserAccount.id" => 'desc');
	 * 
	 * @return Ambigous <array(BaseEntity), multitype:, string, multitype:multitype: >
	 */
	public function findAll($searchActiveOnly = true, $page = null, $pagesize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		$orginal = DaoQuery::$selectActiveOnly;
		if($searchActiveOnly === false)
			DaoQuery::$selectActiveOnly = false;
		$temp = EntityDao::getInstance($this->_entityName)->findAll($page, $pagesize, $orderBy);
		if($searchActiveOnly === false)
			DaoQuery::$selectActiveOnly = $orginal;
		$this->_pageStats = Dao::getPageStats();
		return $temp;
	}
	/**
	 * Finding some entries for that entity
	 * 
	 * @param string $where            The where clause for the sql
	 * @param array  $params           The parameters for PDO exec
	 * @param bool   $searchActiveOnly Whether we will get the active one only
	 * @param int    $page             The page number of the pagination
	 * @param int    $pagesize         The page size of the pagination
	 * @param array  $orderBy          The order by fields. i.e.: array("id" => 'desc');
	 * 
	 * @return Ambigous <array(BaseEntity), BaseEntity, multitype:, string, multitype:multitype: >
	 */
	public function findByCriteria($where, $params = array(), $searchActiveOnly = true, $page = null, $pagesize = DaoQuery::DEFAUTL_PAGE_SIZE, $orderBy = array())
	{
		$orginal = DaoQuery::$selectActiveOnly;
		if($searchActiveOnly === false)
			DaoQuery::$selectActiveOnly = false;
		$temp = EntityDao::getInstance($this->_entityName)->findByCriteria($where, $params, $page, $pagesize, $orderBy);
		if($searchActiveOnly === false)
			DaoQuery::$selectActiveOnly = $orginal;
		$this->_pageStats = Dao::getPageStats();
		return $temp;
	}
	/**
	 * Getting the total count for the search criteria
	 *
	 * @param string $where  The where clause
	 * @param array  $params The parameters
	 *
	 * @return int
	 */
	public function countByCriteria($where, $params)
	{
	    return EntityDao::getInstance($this->_entityName)->countByCriteria($where, $params);
	}
	/**
	 * Updating a table for the search criteria
	 *
	 * @param string $criteria The where clause
	 * @param array  $params   The parameters
	 *
	 * @return int
	 */
	public function updateByCriteria($setClause, $criteria, $params)
	{
	    return EntityDao::getInstance($this->_entityName)->updateByCriteria($setClause, $criteria, $params);
	}
	/**
	 * returning the pagination stats
	 *
	 * @return array
	 */
	public function getPageStats()
	{
	    return $this->_pageStats;
	}
}
?>