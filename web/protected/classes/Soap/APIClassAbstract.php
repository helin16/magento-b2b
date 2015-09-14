<?php
abstract class APIClassAbstract
{
	/**
	 * Result code for success
	 * @var int
	 */
	const RESULT_CODE_SUCC = 0;
	/**
	 * Result code for fail
	 * @var int
	 */
	const RESULT_CODE_FAIL = 1;
	/**
	 * Result code for imcomplete
	 * @var int
	 */
	const RESULT_CODE_IMCOMPLETE = 2;
	/**
	 * Result code for other error
	 * @var int
	 */
	const RESULT_CODE_OTHER_ERROR = 3;
	/**
	 * Focus entity name
	 * 
	 * @var string
	 */
	protected $_entityName = '';
	/**
	 * getting the response
	 * 
	 * @param UDate $time
	 * 
	 * @return SimpleXMLElement
	 */
	protected function _getResponse(UDate $time)
	{
		Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT)); //TODO
		$response = new SimpleXMLElement('<Response />');
		$response->addAttribute('Time', trim($time));
		$response->addAttribute('TimeZone',trim($time->getTimeZone()->getName()));
		return $response;
	}
	/**
	 * Add the CDATA for the xml
	 * 
	 * @param string           $name
	 * @param string           $value
	 * @param SimpleXMLElement $parent
	 * 
	 * @return SimpleXMLElement
	 */
	protected function addCData($name, $value, SimpleXMLElement &$parent) {
		$child = $parent->addChild($name);
		if ($child !== NULL) {
			$child_node = dom_import_simplexml($child);
			$child_owner = $child_node->ownerDocument;
			$child_node->appendChild($child_owner->createCDATASection($value));
		}
		return $child;
	}
	/**
	 * Getting the entity
	 * 
	 * @param int $id The ID of the entity
	 * 
	 * @throws Exception
	 * @return string
	 * @soapmethod
	 */
	public function get($id) {
		$response = $this->_getResponse(UDate::now());
		try {
			$className = trim ($this->_entityName);
			if(!class_exists($className))
				throw new Exception("Entity not exsits: " . $className);
			if(!($instance = $className::get($id)) instanceof $className)
				throw new Exception('Entity for "' . $className . '" not found: ' . $id);
			
			$response['status'] = self::RESULT_CODE_SUCC;
			$this->addCData($className, json_encode($instance->getJson()), $response);
		} catch (Exception $e) {
			$response['status'] = self::RESULT_CODE_FAIL;
			$this->addCData('error', $e->getMessage(), $response);
		}
		return trim($response->asXML());
	}
	/**
	 * Getting all the entities by criteria
	 * 
	 * @param string $where
	 * @param array  $params
	 * @param bool   $activeOnly
	 * @param int    $pageNo
	 * @param int    $pageSize
	 * @param array  $orderArray
	 * 
	 * @throws Exception
	 * @return string
	 * @soapmethod
	 */
	public function getAll($where = '', $params = array(), $activeOnly = null, $pageNo = null, $pageSize = null, $orderArray = array())
	{
		$response = $this->_getResponse(UDate::now());
		try {
			$className = trim ($this->_entityName);
			if(!class_exists($className))
				throw new Exception("Entity not exsits: " . $className);
			$orderArray = ($orderArray === null ? array() : $orderArray);
			$pageSize = ($pageSize === null ? DaoQuery::DEFAUTL_PAGE_SIZE : $pageSize);
			$activeOnly = ($activeOnly === null ? true : $activeOnly);
			$where = ($where === null ? '' : $where);
			
			if(trim($where) === '')
				$instances = $className::getAll($activeOnly, $pageNo, $pageSize, $orderArray);
			else
				$instances = $className::getAllByCriteria(trim($where), $params, $activeOnly, $pageNo, $pageSize, $orderArray);
			$jsonArray = array();
			foreach($instances as $instance)
				$jsonArray[] = $instance->getJson();
			$this->addCData($className . '_array', json_encode($jsonArray), $response);
			$response['status'] = self::RESULT_CODE_SUCC;
		} catch (Exception $e) {
			$response['status'] = self::RESULT_CODE_FAIL;
			$this->addCData('error', $e->getMessage(), $response);
		}
		return trim($response->asXML());
	}
}