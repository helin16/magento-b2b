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
		$js .= ".setCallbackId('priceMatching', '" . $this->priceMatchingBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('toggleItem', '" . $this->toggleItemBtn->getUniqueID() . "')";
		$js .= ".getResults(true, " . $this->pageSize . ");";
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
            $where = array(1);
            $params = array();
            $query = FactoryAbastract::service('Order')->getDao()->getQuery();
            
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
    		var_dump($param->CallbackParameter);die;
    		$class = trim($this->_focusEntity);
    		if(!isset($param->CallbackParameter->item))
    			throw new Exception("System Error: no item information passed in!");
    		$item = (isset($param->CallbackParameter->item->id) && ($item = $class::get($param->CallbackParameter->item->id)) instanceof $class) ? $item : null;
    		$sku = trim($param->CallbackParameter->item->sku);
    		$name = trim($param->CallbackParameter->item->name);
    		$active = $param->CallbackParameter->item->active !== true ? false : true;
    		//$active = (!isset($param->CallbackParameter->item->active) || $param->CallbackParameter->item->active !== true ? false : true);
    			

    		if($item instanceof $class)
    		{
    			$item->setName($sku)
	    			->setDescription($name)
	    			->setActive($active)
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
