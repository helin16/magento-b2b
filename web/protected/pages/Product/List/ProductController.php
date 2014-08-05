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
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'products';
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
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$js .= "pageJs.setCallbackId('getProductList', '" . $this->getProductsBtn->getUniqueID() . "')";
		$js .= ".setHTMLIds('productlist', 'searchPanel', 'total-found-count')";
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
}
?>
