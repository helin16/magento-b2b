<?php
/**
 * This is the listing page for Tasks
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
	public $menuItem = 'tasks';
	protected $_focusEntity = 'Task';
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
		$js .= "pageJs.setHTMLID('searchPanel', 'searchPanel');";
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
		$order = $tech = $customer =null;
		if(isset($_REQUEST['customerId']) && !($customer = Customer::get(trim($_REQUEST['customerId']))) instanceof Customer)
			die('Invalide Customer provided!');
		if(isset($_REQUEST['orderId']) && !($order = Order::get(trim($_REQUEST['orderId']))) instanceof Order)
			die('Invalide Order provided!');
		if(isset($_REQUEST['techId']) && !($tech = UserAccount::get(trim($_REQUEST['techId']))) instanceof UserAccount)
			die('Invalide Technician provided!');

		$allstatuses = isset($_REQUEST['allstatuses']) && (intval(trim($_REQUEST['allstatuses'])) === 1);
		$preSetData = array('statuses' => array(),
				'order' => ($order instanceof Order ? $order->getJson() : array()),
				'technician' => ($tech instanceof UserAccount ? $tech->getJson() : array()),
				'customer' => ($customer instanceof Customer ? $customer->getJson() : array())
		);
		$statuses = array();
		foreach(TaskStatus::getAll() as $status) {
			$statuses[] = ($statusJson = $status->getJson());
			if($allstatuses === false && !in_array(intval($status->getId()), array(TaskStatus::ID_CANCELED, TaskStatus::ID_FINISHED)))
				$preSetData['statuses'][] = $statusJson;
		}
		$js = "pageJs";
			$js .= ".setStatuses(" . json_encode($statuses) . ")";
			$js .= ".setPreSetData(" . json_encode($preSetData) . ")";
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
				$where[] = 'id like :taskId';
				$params['taskId'] = '%' . $taskId . '%';
			}
			if(isset($serachCriteria['ord.id']) && trim($orderIds = $serachCriteria['ord.id']) !== '' && count($orderIds = explode(',', $orderIds)) > 0) {
				$params['entityName'] = 'Order';
				$keys = array();
				foreach($orderIds as $index => $orderId)
				{
					$keys[] = ":" . ($key = "entityId" . $index);
					$params[$key] = $orderId;
				}
				$where[] = 'fromEntityName = :entityName and fromEntityId in (' . implode(', ', $keys) . ')';
			}
			if(isset($serachCriteria['techId']) && trim($techIds = $serachCriteria['techId']) !== '' && count($techIds = explode(',', $techIds)) > 0) {
				$keys = array();
				foreach($techIds as $index => $techId)
				{
					$keys[] = ":" . ($key = "techId" . $index);
					$params[$key] = $techId;
				}
				$where[] = 'technicianId in (' . implode(', ', $keys) . ')';
			}
			if(isset($serachCriteria['customer.id']) && trim($customerIds = $serachCriteria['customer.id']) !== '' && count($customerIds = explode(',', $customerIds)) > 0) {
				$keys = array();
				foreach($customerIds as $index => $customerId)
				{
					$keys[] = ":" . ($key = "customerId" . $index);
					$params[$key] = $customerId;
				}
				$where[] = 'customerId in (' . implode(', ', $keys) . ')';
			}
			if(isset($serachCriteria['statusId']) && count($statusIds = $serachCriteria['statusId']) > 0) {
				$keys = array();
				foreach($statusIds as $index => $statusId)
				{
					$keys[] = ":" . ($key = "statusId" . $index);
					$params[$key] = $statusId;
				}
				$where[] = 'statusId in (' . implode(', ', $keys) . ')';
			}
			if(isset($serachCriteria['dueDate_from']) && ($dueDate_from = new UDate($serachCriteria['dueDate_from'])) !== false) {
				$where[] = 'dueDate >= :dueDateFrom';
				$params['dueDateFrom'] = $dueDate_from->format('Y-m-d') . ' 00:00:00';
			}
			if(isset($serachCriteria['dueDate_to']) && ($dueDate_to = new UDate($serachCriteria['dueDate_to'])) !== false) {
				$where[] = 'dueDate <= :dueDateTo';
				$params['dueDateTo'] = $dueDate_from->format('Y-m-d') . ' 23:59:59';
			}
			$stats = array();
			$objects = $class::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('dueDate' => 'asc'), $stats);
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
}
?>
