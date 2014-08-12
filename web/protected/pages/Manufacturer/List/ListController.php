<?php
/**
 * This is the listing page for manufacturer
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
	public $menuItem = 'manufacturers';
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
	 * Getting the orders
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
			if(isset($serachCriteria['man.name']) && ($name = trim($serachCriteria['man.name'])) !== '')
			{
				$where[] = 'man.name like ?';
				$params[] = '%' . $name . '%';
			}
			$objects = Manufacturer::getAllByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('man.name' => 'asc'));
			$results['pageStats'] = FactoryAbastract::service('Product')->getPageStats();
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
			$ids = isset($param->CallbackParameter->ids) ? $param->CallbackParameter->ids : array();
			if(count($ids) > 0)
				Manufacturer::deleteByCriteria('id in (' . str_repeat('?', count($ids)) . ')', $ids);
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
			if(!isset($param->CallbackParameter->item))
				throw new Exception("System Error: no item information passed in!");
			$item = (isset($param->CallbackParameter->item->id) && ($item = Manufacturer::get($param->CallbackParameter->item->id)) instanceof Manufacturer) ? $item : null;
			$name = trim($param->CallbackParameter->item->name);
			$description = trim($param->CallbackParameter->item->description);
			if($item instanceof Manufacturer)
			{
				$item->setName($name)
					->setName($description)
					->save();
			}
			else
			{
				$item = Manufacturer::create($name, $description, false);
			}
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
