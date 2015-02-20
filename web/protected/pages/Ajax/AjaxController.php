<?php
/**
 * Ajax Controller
 * 
 * @package	web
 * @subpackage	Controller-Page
 * 
 * @version	1.0
 * 
 * @todo :NOTE If anyone copies this controller, then you require this method to profile ajax requests
 */
class AjaxController extends TService 
{
  	/**
  	 * Run
  	 */
  	public function run() 
  	{
//   		if(!($this->getUser()->getUserAccount() instanceof UserAccount))
//   			die (BPCPageAbstract::show404Page('Invalid request',"No defined access."));
  		
  		if(!isset($this->Request['method']) || ($method = trim($this->Request['method'])) === '' || !method_exists($this, ($method = '_' .$method)))
  			die (BPCPageAbstract::show404Page('Invalid request',"No method passed in."));
  		
  		$results = $errors = array();
		try
		{
			$results = $this->$method($_REQUEST);
		} 
		catch (Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$this->getResponse()->flush();
        $this->getResponse()->appendHeader('Content-Type: application/json');
        $this->getResponse()->write(StringUtilsAbstract::getJson($results, $errors));
  	}
	/**
	 * Getting the comments for an entity
	 * 
	 * @param array $params
	 * 
	 * @return string The json string
	 */  	
  	private function _getComments(Array $params)
  	{
  		if(!isset($params['entityId']) || !isset($params['entity']) || ($entityId = trim($params['entityId'])) === '' || ($entity = trim($params['entity'])) === '')
  			throw  new Exception('SYSTEM ERROR: INCOMPLETE DATA PROVIDED');
  		
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : 1);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array('created' => 'desc'));
  		
  		$where ='entityName = ? and entityId = ?';
  		$sqlParams = array($entity, $entityId);
  		if(isset($params['type']) && ($commentType = trim($params['type'])) !== '')
  		{
  			$where .= 'and type = ?';
  			$sqlParams[] = trim($commentType);
  		}
  		$returnArray = json_encode(array());
  		$stats = array();
  		$commentsArray = Comments::getAllByCriteria($where, $sqlParams, true, $pageNo, $pageSize, $orderBy, $stats);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $commentsArray);
  		$results['pageStats'] = $stats;
  		return $results;
  	}
  	
  	/**
  	 * Getting the comments for an entity
  	 *
  	 * @param array $params
  	 *
  	 * @return string The json string
  	 */
  	private function _getCustomers(Array $params)
  	{
  		$searchTxt = trim(isset($params['searchTxt']) ? $params['searchTxt'] : '');
  		if($searchTxt === '')
  			throw new Exception('SearchTxt is needed');
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : null);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array());
  	
  		$where = array('name like :searchTxt or email like :searchTxt or contactNo like :searchTxt');
  		$sqlParams = array('searchTxt' => '%' . $searchTxt . '%');
  		$stats = array();
  		$items = Customer::getAllByCriteria(implode(' AND ', $where), $sqlParams, true, $pageNo, $pageSize, $orderBy, $stats);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $items);
  		$results['pageStats'] = $stats;
  		return $results;
  	}

}




?>