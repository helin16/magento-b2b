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

  		$results = $errors = array();
        try
        {
            $method = '_' . ((isset($this->Request['method']) && trim($this->Request['method']) !== '') ? trim($this->Request['method']) : '');
            if(!method_exists($this, $method))
                throw new Exception('No such a method: ' . $method . '!');

            $results = $this->$method($_REQUEST);
        }
        catch (Exception $ex)
        {
        	$errors = $ex->getMessage();
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
  	 * Getting the delivery methods
  	 *
  	 * @param unknown $params
  	 *
  	 * @return multitype:multitype:
  	 */
  	private function _getDeliveryMethods($params)
  	{
  		$searchTxt = (isset($params['searchTxt']) && ($searchTxt = trim($params['searchTxt'])) !== '' ? $searchTxt : '');
  		$sql = 'select distinct value from orderinfo where value like ? and active = 1 and typeId = ' . OrderInfoType::ID_MAGE_ORDER_SHIPPING_METHOD;
  		$results = array();
  		$results['items'] = array_map(create_function('$a', 'return $a["value"];'), Dao::getResultsNative($sql, array('%' . trim($searchTxt) . '%'), PDO::FETCH_ASSOC));
  		return $results;
  	}

  	private function _getCustomer($params)
  	{
  		$searchTxt = (isset($params['searchTxt']) && ($searchTxt = trim($params['searchTxt'])) !== '' ? $searchTxt : '');
  		$pageSize = (isset($params['pageSize']) && ($pageSize = trim($params['pageSize'])) !== '' ? $pageSize : DaoQuery::DEFAUTL_PAGE_SIZE);
  		$pageNo = (isset($params['pageNo']) && ($pageNo = trim($params['pageNo'])) !== '' ? $pageNo : 1);
  		$orderBy = (isset($params['orderBy']) ? $params['orderBy'] : array('cust.name' => 'asc'));

  		$stats = array();
  		$customers = Customer::getAllByCriteria('name like ?', array('%' . $searchTxt . '%'), true, $pageNo, $pageSize, $orderBy, $stats);
  		$results['items'] = array_map(create_function('$a', 'return $a->getJson();'), $customers);
  		$results['pageStats'] = $stats;
  		return $results;
  	}
}




?>