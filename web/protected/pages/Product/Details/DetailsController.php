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
		$js .= "pageJs.setManufactures(" . json_encode($manufacturers) . ")";
		$js .= ".setSuppliers(" . json_encode($suppliers) . ")";
		$js .= ".setStatuses(" . json_encode($statuses) . ")";
		$js .= ".setPriceTypes(" . json_encode($priceTypes) . ")";
		$js .= ".load()";
		$js .= ".bindAllEventNObjects();";
		return $js;
	}
}
?>
