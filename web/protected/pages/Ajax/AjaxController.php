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
  		
		try
		{
			echo $this->$method($_REQUEST);
		} 
		catch (Exception $ex)
		{
			echo $ex->getMessage();
		}
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
  		
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : 1);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$orderBy = array(); //TODO: need to figure out a way to pass in the order by params
  		
  		$where ='entityName = ? and entityId = ?';
  		$params = array($entity, $entityId);
  		if(isset($params['type']) && ($commentType = $params['type']) !== '')
  		{
  			$where .= 'and type = ?';
  			$params[] = trim($commentType);
  		}
  		$returnArray = json_encode(array());
  		$commentsArray = FactoryAbastract::service('Comments')->findByCriteria($where, $params, true, $pageSize, $pageNo, $orderBy);
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $commentsArray);
  		$results['pageStats'] = FactoryAbastract::service('OrderItem')->getPageStats();
  		return json_encode($results);
  	}

}




?>