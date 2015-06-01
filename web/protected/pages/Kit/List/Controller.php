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
		$order = $task = $customer =null;
		if(isset($_REQUEST['customerId']) && !($customer = Customer::get(trim($_REQUEST['customerId']))) instanceof Customer)
			die('Invalid Customer provided!');
		if(isset($_REQUEST['taskId']) && !($task = Task::get(trim($_REQUEST['taskId']))) instanceof Task)
			die('Invalid Order provided!');
		if(isset($_REQUEST['orderId']) && !($order = Order::get(trim($_REQUEST['orderId']))) instanceof Order)
			die('Invalid Order provided!');
		$preSetData = array(
				'task' => ($task instanceof Task ? $task->getJson() : array()),
				'order' => ($order instanceof Order ? $order->getJson() : array()),
				'customer' => ($customer instanceof Customer ? $customer->getJson() : array())
		);
		$js = "pageJs";
			$js .= ".setOpenInFancyBox(" . ((isset($_REQUEST['blanklayout']) && (intval(trim($_REQUEST['blanklayout'])) === 1) && (isset($_REQUEST['nosearch']) && intval($_REQUEST['nosearch']) === 1)) ? 'false' : 'true') . ")";
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
			if(isset($serachCriteria['barcode']) && trim($barcode = $serachCriteria['barcode']) !== '') {
				$where[] = 'barcode like :barcode';
				$params['barcode'] = '%' . $barcode . '%';
			}
			if(isset($serachCriteria['taskIds']) && trim($taskIds= $serachCriteria['taskIds']) !== '' && count($taskIds = explode(',', $taskIds)) > 0) {
				$keys = array();
				foreach($taskIds as $index => $taskId)
				{
					$keys[] = ":" . ($key = "taskId" . $index);
					$params[$key] = $taskId;
				}
				$where[] = 'taskId in (' . implode(', ', $keys) . ')';
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
			if(isset($serachCriteria['kitProductIds']) && trim($kitProductIds = $serachCriteria['kitProductIds']) !== '' && count($kitProductIds = explode(',', $kitProductIds)) > 0) {
				$keys = array();
				foreach($kitProductIds as $index => $kitProductId)
				{
					$keys[] = ":" . ($key = "kitProductId" . $index);
					$params[$key] = $kitProductId;
				}
				$where[] = 'productId in (' . implode(', ', $keys) . ')';
			}
			if(isset($serachCriteria['componentProductIds']) && trim($componentProductIds = $serachCriteria['componentProductIds']) !== '' && count($componentProductIds = explode(',', $componentProductIds)) > 0) {
				$keys = array();
				foreach($componentProductIds as $index => $componentProductId)
				{
					$keys[] = ":" . ($key = "componentProductId" . $index);
					$params[$key] = $componentProductId;
				}
				$where[] = 'id in (select kitcom.kitId from kitcomponent kitcom where kitcom.componentId in (' . implode(', ', $keys) . '))';
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
