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
		$js .= "$('searchBtn').click();";
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
			if(isset($serachCriteria['ord.id']) && ($orderId = trim($serachCriteria['ord.id'])) !== '')
			{
				$where[] = 'fromEntityName = :entityName and fromEntityId = :entityId';
				$params['entityName'] = 'Order';
				$params['entityId'] = $orderId;
			}
			if(isset($serachCriteria['techId']) && ($techId = trim($serachCriteria['techId'])) !== '')
			{
				$where[] = 'technicianId = :techId';
				$params['techId'] = $techId;
			}
			if(isset($serachCriteria['statusId']) && ($statusId = trim($serachCriteria['statusId'])) !== '')
			{
				$where[] = 'statusId = :statusId';
				$params['statusId'] = $statusId;
			}
			if(isset($serachCriteria['dueDate_from']) && ($dueDate_from = new UDate($serachCriteria['dueDate_from'])) !== false)
			{
				$where[] = 'dueDate >= :dueDateFrom';
				$params['dueDateFrom'] = $dueDate_from->format('Y-m-d') . ' 00:00:00';
			}
			if(isset($serachCriteria['dueDate_to']) && ($dueDate_to = new UDate($serachCriteria['dueDate_to'])) !== false)
			{
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
	/**
	 * save the items
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function saveItem($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$class = trim($this->_focusEntity);
			if(!isset($param->CallbackParameter->item))
				throw new Exception("System Error: no item information passed in!");
			$item = (isset($param->CallbackParameter->item->id) && ($item = $class::get($param->CallbackParameter->item->id)) instanceof $class) ? $item : null;
			$value = trim($param->CallbackParameter->item->value);
			if(!$item instanceof $class)
				throw new Exception("System Error: Invalid id passed in");
			$item->setValue($value)
				->save();
			$results['item'] = $item->getJson();
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
