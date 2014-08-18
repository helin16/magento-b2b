<?php
/**
 * This is the Product details page
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class DetailsController extends DetailsPageAbstract
{
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$menuItem
	 */
	public $menuItem = 'products';
	/**
	 * (non-PHPdoc)
	 * @see BPCPageAbstract::$_focusEntityName
	 */
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
	protected function _getEndJs()
	{
		$js = parent::_getEndJs();
		$manufacturers = array_map(create_function('$a', 'return $a->getJson();'), Manufacturer::getAll());
		$suppliers = array_map(create_function('$a', 'return $a->getJson();'), Supplier::getAll());
		$statuses = array_map(create_function('$a', 'return $a->getJson();'), ProductStatus::getAll());
		$priceTypes = array_map(create_function('$a', 'return $a->getJson();'), ProductPriceType::getAll());
		$codeTypes = array_map(create_function('$a', 'return $a->getJson();'), ProductCodeType::getAll());
		
		$categories = array();
		foreach(ProductCategory::getAllByCriteria('parentId is null', array()) as $category)
		{
			$categories[] = $this->_getCategoryJson($category);
		}
		$js .= "pageJs.setPreData(" . json_encode($manufacturers) . ", " . json_encode($suppliers) . ", " . json_encode($statuses) . ", " . json_encode($priceTypes) . ", " . json_encode($codeTypes) . "," . json_encode($categories) . ")";
		$js .= ".load()";
		$js .= ".bindAllEventNObjects();";
		return $js;
	}
	private function _getCategoryJson(ProductCategory $category)
	{
		$categoryJson = $category->getJson();
		$children = array();
		$categories = ProductCategory::getAllByCriteria('parentId = ?', array($category->getId()));
		foreach($categories as $cate)
		{
			$children[] = $this->_getCategoryJson($cate);
		}
		$categoryJson['children'] = $children;
		return $categoryJson;
	}
}
?>
