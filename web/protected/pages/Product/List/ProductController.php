<?php
/**
 * This is the ProductController
 * 
 * @package    Web
 * @subpackage Controller
 * @author     lhe<helin16@gmail.com>
 */
class ProductController extends CRUDPageAbstract
{
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
	protected function _getEndJs()
	{
		foreach (Manufacturer::getAll() as $os)
			$manufactureArray[] = $os->getJson();
		foreach (Supplier::getAll() as $os)
			$supplierArray[] = $os->getJson();
		foreach (ProductCategory::getAll() as $os)
			$productCategoryArray[] = $os->getJson();
		
		$js = parent::_getEndJs();
		$js .= 'pageJs._loadManufactures('.json_encode($manufactureArray).');';
		$js .= 'pageJs._loadSuppliers('.json_encode($supplierArray).');';
		$js .= 'pageJs._loadProductCategories('.json_encode($productCategoryArray).')';
		$js .= "._loadChosen()";
		$js .= "._bindSearchKey()";
		$js .= ".setCallbackId('priceMatching', '" . $this->priceMatchingBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('toggleItem', '" . $this->toggleItemBtn->getUniqueID() . "')";
		$js .= ".getResults(true, " . $this->pageSize . ");";
		$js .= '$("searchBtn").click();';
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
            if(!isset($param->CallbackParameter->searchCriteria) || count($serachCriteria = json_decode(json_encode($param->CallbackParameter->searchCriteria), true)) === 0)
                throw new Exception('System Error: search criteria not provided!');
            $pageNo = 1;
            $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;
            
            if(isset($param->CallbackParameter->pagination))
            {
                $pageNo = $param->CallbackParameter->pagination->pageNo;
                $pageSize = $param->CallbackParameter->pagination->pageSize * 3;
            }
            
            $stats = array();
            $objects = Product::getProducts(trim($serachCriteria['pro.sku']), trim($serachCriteria['pro.name']), array(), array(), array(), array(), trim($serachCriteria['pro.active']), $pageNo, $pageSize, array('pro.name' => 'asc'), $stats);
            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
                $results['items'][] = $obj->getJson();
        }
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() . $ex->getTraceAsString();
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
    public function toggleItem($sender, $param)
    {
    	$results = $errors = array();
    	try
    	{
    		$class = trim($this->_focusEntity);
    		$product = Product::get($param->CallbackParameter->id);
    		$item = (isset($param->CallbackParameter->id) && ($item = $class::get($param->CallbackParameter->id)) instanceof $class) ? $item : null;
    		$active = $param->CallbackParameter->active;
    		if($item instanceof $class)
    		{
    			$item->setActive($active)
	    			->save();
    		}
    		$results['item'] = $item->getJson();
    	}
    	catch(Exception $ex)
    	{
    		$errors[] = $ex->getMessage();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
    public function priceMatching($sender, $param)
    {
    	$results = $errors = array();
    	try
    	{
    		$id = isset($param->CallbackParameter->id) ? $param->CallbackParameter->id : '';
    		$product = Product::get($id);
    		$prices = ProductPrice::getPrices($product, ProductPriceType::get(ProductPriceType::ID_RRP));
    		$companies = PriceMatcher::getAllCompaniesForPriceMatching();
    		$prices = PriceMatcher::getPrices($companies, $product->getSku(), (count($prices)===0 ? 0 : $prices[0]->getPrice()) );
    		$myPrice = $prices['myPrice'];
    		$minPrice = $prices['minPrice'];
    		$msyPrice = $prices['companyPrices']['MSY'];
    		$prices['id'] = $id;
    		$results = $prices;
    		//echo $prices;
    	}
    	catch(Exception $ex)
    	{
    		$errors[] = $ex->getMessage();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
}
?>
