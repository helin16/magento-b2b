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
  		
		$this->$method($_REQUEST);
  	}

  	private function _test(Array $params)
  	{
  		var_dump($params);
  	}
  	
  	private function _getComments(Array $params)
  	{
  		if(!isset($params['entityId']) || !isset($params['entity']) || ($entityId = trim($params['entityId'])) === '' || ($entity = trim($params['entity'])) === '')
  		{	
  			echo 'SYSTEM ERROR: INCOMPLETE DATA PROVIDED';
  			return;
  		}
  		if(!isset($params['type']) || ($commentType = $params['type']) === '')
  			$commentType = 'NORMAL';
  		
  		$returnArray = json_encode(array());
  		$commentsArray = FactoryAbastract::service('Comments')->findByCriteria('entityName = ? and entityId = ? and type = ?', array($entity, $entityId, $commentType), true);
  		if(count($commentsArray) > 0)
  			$returnArray = json_encode(array_map(create_function('$a', 'return $a->getJson();'), $commentsArray));
  		echo $returnArray;
  		return;
  	}

}




?>