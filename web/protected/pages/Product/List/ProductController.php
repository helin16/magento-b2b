<?php
/**
 * This is the ProductController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ProductController extends BPCPageAbstract
{
	public $pageSize = 30;
	/**
	 * @var TCallback
	 */
	private $_saveItemsBtn;
	/**
	 * @var TCallback
	 */
	private $_delItemsBtn;
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'products';
	protected $_focusEntity = 'Product';
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
	 * Getting The end javascript
	 *
	 * @return string
	 */
	public function onInit($param)
	{
		parent::onInit($param);
		
		$this->_saveItemsBtn = new TCallback();
		$this->_saveItemsBtn->ID = 'saveItemBtn';
		$this->_saveItemsBtn->OnCallback = 'Page.saveItem';
		$this->getControls()->add($this->_saveItemsBtn);
	
		$this->_delItemsBtn = new TCallback();
		$this->_delItemsBtn->ID = 'delItemsBtn';
		$this->_delItemsBtn->OnCallback = 'Page.deleteItems';
		$this->getControls()->add($this->_delItemsBtn);
	}
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs.setCallbackId('getProductList', '" . $this->getProductsBtn->getUniqueID() . "');";
		$js .= "pageJs.setCallbackId('deleteItems', '" . $this->_delItemsBtn->getUniqueID() . "');";
		$js .= "pageJs.setCallbackId('saveItem', '" . $this->_saveItemsBtn->getUniqueID() . "');";
		$js .= "pageJs.setHTMLIds('productlist', 'searchPanel', 'total-found-count')";
		$js .= ";";
		$js .= '$("searchBtn").click();';
		return $js;
	}
	/**
	 * Getting the orders
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function getProducts($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$class = trim($this->_focusEntity);
			if(!isset($param->CallbackParameter->searchCriteria) || count($serachCriteria = json_decode(json_encode($param->CallbackParameter->searchCriteria), true)) === 0)
				throw new Exception('System Error: search criteria not provided!');
			$pageNo = 1;
			$pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
			if(isset($param->CallbackParameter->pagination))
			{
				$pageNo = $param->CallbackParameter->pagination->pageNo;
				$pageSize = $param->CallbackParameter->pagination->pageSize;
			}
				
			$where = array(1);
			$params = array();
			if(($sku = trim($serachCriteria['pro.sku'])) !== '')
			{
				$where[] = 'pro.sku like ?';
				$params[] = $sku . '%';
			}
			if(($name = trim($serachCriteria['pro.name'])) !== '')
			{
				$where[] = 'pro.name like ?';
				$params[] = $name . '%';
			}
			if(($active = trim($serachCriteria['pro.active'])) !== '')
			{
				$where[] = 'pro.active = ?';
				$params[] = $active;
			}
			$objects = FactoryAbastract::service('Product')->findByCriteria(implode(' AND ', $where), $params, false, $pageNo, $pageSize, array('pro.name' => 'asc'));
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
			$class = trim($this->_focusEntity);
			$ids = isset($param->CallbackParameter->ids) ? $param->CallbackParameter->ids : array();
			if(count($ids) > 0)
				$class::deleteByCriteria('id in (' . str_repeat('?', count($ids)) . ')', $ids);
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
			$sku = trim($param->CallbackParameter->item->sku);
			$name = trim($param->CallbackParameter->item->name);
			if($item instanceof $class)
			{
				$item->setName($sku)
				->setDescription($name)
				->save();
			}
			else
			{
				$item = $class::create($sku, $name, false);
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
