<?php
/**
 * This is the listing page for Kits
 *
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class Controller extends CRUDPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'kits';
	protected $_focusEntity = 'Kit';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(!AccessControl::canAccessWorkShopPage(Core::getRole()))
			die('You do NOT have access to this page');
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs.setHTMLID('searchPanel', 'searchPanel')";
		$js .= ";";
		$js .= "$('searchBtn').click();";
		if(isset($_REQUEST['nosearch']) && intval($_REQUEST['nosearch']) === 1)
			$js .= "$(pageJs.getHTMLID('searchPanel')).hide();";
		return $js;
	}
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::_preGetEndJs()
	 */
	protected function _preGetEndJs()
	{
// 		parent::_preGetEndJs();
// 		$order = $tech = $customer =null;
// 		if(isset($_REQUEST['customerId']) && !($customer = Customer::get(trim($_REQUEST['customerId']))) instanceof Customer)
// 			die('Invalid Customer provided!');
// 		if(isset($_REQUEST['orderId']) && !($order = Order::get(trim($_REQUEST['orderId']))) instanceof Order)
// 			die('Invalid Order provided!');
// 		if(isset($_REQUEST['techId']) && !($tech = UserAccount::get(trim($_REQUEST['techId']))) instanceof UserAccount)
// 			die('Invalid Technician provided!');
// 		$statusIds = array();
// 		if(isset($_REQUEST['statusIds']) && ($statusIds = trim($_REQUEST['statusIds'])) !== '')
// 			$statusIds = array_map(create_function('$a', 'return intval(trim($a));'), explode(',', $statusIds));
// 		$allstatuses = isset($_REQUEST['allstatuses']) && (intval(trim($_REQUEST['allstatuses'])) === 1);
// 		$preSetData = array('statuses' => array(),
// 				'order' => ($order instanceof Order ? $order->getJson() : array()),
// 				'technician' => ($tech instanceof UserAccount ? $tech->getJson() : array()),
// 				'customer' => ($customer instanceof Customer ? $customer->getJson() : array()),
// 				'meId' => Core::getUser()->getId(),
// 				'noDueDateStatusIds' => array()
// 		);
// 		$statuses = array();
// 		foreach(TaskStatus::getAll() as $status) {
// 			$statuses[] = ($statusJson = $status->getJson());
// 			if(($noDueDateChecking = in_array(intval($status->getId()), TaskStatus::getClosedStatusIds())) === true)
// 				$preSetData['noDueDateStatusIds'][] = $status->getId();
// 			if(count($statusIds) > 0) {
// 				if(in_array(intval($status->getId()), $statusIds))
// 					$preSetData['statuses'][] = $statusJson;
// 			}
// 			else if($allstatuses === false && !$noDueDateChecking)
// 				$preSetData['statuses'][] = $statusJson;
// 		}
// 		if(count($statusIds) > 0 && count($preSetData['statuses']) === 0)
// 			die('Invalide Task Status provided.');
		$js = "pageJs";
// 			$js .= ".setOpenInFancyBox(" . ((isset($_REQUEST['blanklayout']) && (intval(trim($_REQUEST['blanklayout'])) === 1) && (isset($_REQUEST['nosearch']) && intval($_REQUEST['nosearch']) === 1)) ? 'false' : 'true') . ")";
// 			$js .= ".setStatuses(" . json_encode($statuses) . ")";
// 			$js .= ".setPreSetData(" . json_encode($preSetData) . ")";
		$js .= ";";
		return $js;
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function getItems($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$class = trim($this->_focusEntity);
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}

			$serachCriteria = isset($param->CallbackParameter->searchCriteria) ? json_decode(json_encode($param->CallbackParameter->searchCriteria), true) : array();
			$where = array(1);
			$params = array();
			if(isset($serachCriteria['taskId']) && trim($taskId = $serachCriteria['taskId']) !== '') {
				$where[] = 'taskId like :taskId';
				$params['taskId'] = '%' . $taskId . '%';
			}
			if(isset($serachCriteria['ord.id']) && trim($orderIds = $serachCriteria['ord.id']) !== '' && count($orderIds = explode(',', $orderIds)) > 0) {
				$keys = array();
				foreach($orderIds as $index => $orderId)
				{
					$keys[] = ":" . ($key = "orderId" . $index);
					$params[$key] = $orderId;
				}
				$where[] = 'soldOnOrderId in (' . implode(', ', $keys) . ')';
			}
			if(isset($serachCriteria['customer.id']) && trim($customerIds = $serachCriteria['customer.id']) !== '' && count($customerIds = explode(',', $customerIds)) > 0) {
				$keys = array();
				foreach($customerIds as $index => $customerId)
				{
					$keys[] = ":" . ($key = "customerId" . $index);
					$params[$key] = $customerId;
				}
				$where[] = 'soldToCustomerId in (' . implode(', ', $keys) . ')';
			}
			$stats = array();
			$objects = $class::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('id' => 'desc'), $stats);
			$results['pageStats'] = $stats;
			$results['items'] = array();
			foreach($objects as $obj)
				$results['items'][] = $obj->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * Getting the items
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function actionTask($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			if(!isset($param->CallbackParameter->taskId) || !($task = Task::get(trim($param->CallbackParameter->taskId))) instanceof Task)
				throw new Exception('Invalid Task provided!');
			if(!isset($param->CallbackParameter->method) || ($method = trim(trim($param->CallbackParameter->method))) === '' || !method_exists($task, $method))
				throw new Exception('Invalid Action Method!');
			$comments = (isset($param->CallbackParameter->comments) ? trim($param->CallbackParameter->comments) : '');
			$results['item'] = $task->$method(Core::getUser(), $comments)->getJson();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
