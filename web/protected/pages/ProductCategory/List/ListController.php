<?php
/**
 * This is the listing page for ProductCodeType
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ListController extends CRUDPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'productcategories';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEnttiy
	 */
	protected $_focusEntity = 'ProductCategory';
	/**
	 * constructor
	 */
	public function __construct()
	{
		parent::__construct();
		if(!AccessControl::canAccessProductsPage(Core::getRole()))
			die('You do NOT have access to this page');
	}
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs.getResults(true, " . $this->pageSize . ");";
		$js .= "pageJs._bindSearchKey();";
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
			$parent = (isset($serachCriteria['parentId']) && ($parent = $class::get($serachCriteria['parentId'])) instanceof $class) ? $parent : null; 
				
// 			$where = array(!$parent instanceof $class ? 'rootId = id' : 'parentId = ' . $parent->getId());
			$where = array();
			$params = array();
			var_dump(count($serachCriteria));
			if(isset($serachCriteria['name']) && ($name = trim($serachCriteria['name'])) !== '')
			{
				$where[] = 'name like ?';
				$params[] = '%' . $name . '%';
			}
			if(isset($serachCriteria['mageId']) && ($mageId = trim($serachCriteria['mageId'])) !== '')
			{
				$where[] = 'mageId = ?';
				$params[] = $mageId;
			}
			$stats = array();
			$objects = $class::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('pro_cate.position' => 'asc'), $stats);
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
			$name = trim($param->CallbackParameter->item->name);
			$description = trim($param->CallbackParameter->item->description);
			$parent = (isset($param->CallbackParameter->item->parentId) && ($parent = $class::get($param->CallbackParameter->item->parentId)) instanceof $class) ? $parent : null;
			if($item instanceof $class)
			{
				$item->setName($name)
					->setDescription($description)
					->setParent($parent)
					->save();
			}
			else
			{
				$item = $class::create($name, $description, $parent);
			}
			$results['item'] = $item->getJson();
			$results['parent'] = ($parent instanceof $class ? $parent->getJson() : null);
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * delete the items
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function deleteItems($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$class = trim($this->_focusEntity);
			$ids = isset($param->CallbackParameter->ids) ? $param->CallbackParameter->ids : array();
			if(count($ids) > 0)
			{	
				$parents = array();
				foreach($ids as $id)
				{
					if(!($item = $class::get($id)) instanceof $class)
						continue;
					$item->setActive(false)
						->save();
					$parents[] = $item->getParent()->getJson();
				}
				$class::deleteByCriteria('id in (' . str_repeat('?', count($ids)) . ')', $ids);
				$results['parents'] = $parents;
			}
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
