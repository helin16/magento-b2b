<?php
abstract class APIServiceAbstract
{
    /**
     * The focus entity name
     *
     * @var string
     */
    protected $entityName = '';
    /**
     * The APIService
     *
     * @var APIService
     */
    protected $_runner = null;
    /**
     * constructor
     *
     * @param APIService $runner
     */
    public function __construct(APIService $runner)
    {
        $this->_runner = $runner;
    }
  	/**
  	 * Getting an entity
  	 *
  	 * @param unknown $params
  	 *
  	 * @throws Exception
  	 * @return multitype:
  	 */
  	public function get_id($params)
  	{
  		$entityName = trim($this->entityName);
  		if(!isset($params['entityId']) || ($entityId = trim($params['entityId'])) === '')
  			throw new Exception('What are we going to get with?');
  		if(!($entity = $entityName::get($entityId)) instanceof BaseEntityAbstract)
  		    throw new Exception('There is no such a ' . $entityName);
  		return $entity->getJson();
  	}
  	/**
  	 * Getting All for entity
  	 *
  	 * @param unknown $params
  	 *
  	 * @throws Exception
  	 * @return multitype:multitype:
  	 */
  	public function get_all($params)
  	{
  		$entityName = trim($this->entityName);

  		$searchTxt = trim(isset($params['searchTxt']) ? trim($params['searchTxt']) : 'active = 1');
  		$searchParams = isset($params['searchParams']) ? $params['searchParams'] : array();
  		$pageNo = isset($params['pageNo']) ? trim($params['pageNo']) : 1;
  		$pageSize = isset($params['pageSize']) ? trim($params['pageSize']) : DaoQuery::DEFAUTL_PAGE_SIZE;
  		$active = isset($params['active']) ? intval($params['active']) : 1;
  		$orderBy = isset($params['orderBy']) ? trim($params['orderBy']) : array();

  		$stats = array();
  		$items = $entityName::getAllByCriteria($searchTxt, $searchParams, $active, $pageNo, $pageSize, $orderBy, $stats);
  		$return = array();
  		foreach($items as $item)
  		    $return[] = $item->getJson();
  		return array('items' => $return, 'pagination' => $stats);
  	}
  	/**
  	 * Getting the value from params
  	 *
  	 * @param array  $params
  	 * @param string $key
  	 * @param mixed  $defultValue
  	 * @param bool   $compulsory
  	 *
  	 * @throws Exception
  	 * @return unknown
  	 */
  	protected function _getPram($params, $key, $defultValue = null, $compulsory = false)
  	{
  	    if(!isset($params[$key])) {
  	        if($compulsory === true)
  	            throw new Exception($key . ' is NOT Set');
  	        return $defaultValue;
  	    }
  	    return $params[$key];
  	}

}