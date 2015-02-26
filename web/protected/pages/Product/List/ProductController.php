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
	/**
	 * (non-PHPdoc)
	 * @see CRUDPageAbstract::_getEndJs()
	 */
	protected function _getEndJs()
	{
		$manufactureArray = $supplierArray = $statuses = $productCategoryArray = array();
		foreach (Manufacturer::getAll() as $os)
			$manufactureArray[] = $os->getJson();
		foreach (Supplier::getAll() as $os)
			$supplierArray[] = $os->getJson();
		foreach (ProductStatus::getAll() as $os)
			$statuses[] = $os->getJson();
		foreach (ProductCategory::getAll() as $os)
			$productCategoryArray[] = $os->getJson();

		$js = parent::_getEndJs();
		if(($product = Product::get($this->Request['id']))  instanceof Product) {
			$js .= "$('searchPanel').hide();";
			$js .= "pageJs._singleProduct = true;";
		}
		$js .= 'pageJs._loadManufactures('.json_encode($manufactureArray).')';
		$js .= '._loadSuppliers('.json_encode($supplierArray).')';
		$js .= '._loadCategories('.json_encode($productCategoryArray).')';
		$js .= '._loadProductStatuses('.json_encode($statuses).')';
		$js .= "._loadChosen()";
		$js .= "._bindSearchKey()";
		$js .= ".setCallbackId('priceMatching', '" . $this->priceMatchingBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('toggleActive', '" . $this->toggleActiveBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('updatePrice', '" . $this->updatePriceBtn->getUniqueID() . "')";
		$js .= ".getResults(true, " . $this->pageSize . ");";
		return $js;
	}
	public function getRequestProductID()
	{
		return ($product = Product::get($this->Request['id']))  instanceof Product ? $product->getId() : '';
	}
	/**
	 * Updating the full description of the product
	 *
	 * @param Product $product
	 * @param unknown $param
	 *
	 * @return ProductController
	 */
	private function _updateFullDescription(Product &$product, $param)
	{
		//update full description
		if(isset($param->CallbackParameter->fullDescription) && ($fullDescription = trim($param->CallbackParameter->fullDescription)) !== '')
		{
			if(($fullAsset = Asset::getAsset($product->getFullDescAssetId())) instanceof Asset)
				Asset::removeAssets(array($fullAsset->getAssetId()));
			$fullAsset = Asset::registerAsset('full_description_for_product.txt', $fullDescription, Asset::TYPE_PRODUCT_DEC);
			$product->setFullDescAssetId($fullAsset->getAssetId());
		}
		return $this;
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
            if(isset($serachCriteria['pro.id']) && ($product = Product::get($serachCriteria['pro.id'])) instanceof Product) {
            	$objects = array($product);
            	$stats = array('totalPages' => 1);
            } else {
	            $pageNo = 1;
	            $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;

	            if(isset($param->CallbackParameter->pagination))
	            {
	                $pageNo = $param->CallbackParameter->pagination->pageNo;
	                $pageSize = $param->CallbackParameter->pagination->pageSize * 3;
	            }

	            $stats = array();
	            $categoryIds = (!isset($serachCriteria['pro.productCategoryIds']) || is_null($serachCriteria['pro.productCategoryIds'])) ? array() : $serachCriteria['pro.productCategoryIds'];
	            $supplierIds = (!isset($serachCriteria['pro.supplierIds']) || is_null($serachCriteria['pro.supplierIds'])) ? array() : $serachCriteria['pro.supplierIds'];
	            $manufacturerIds = (!isset($serachCriteria['pro.manufacturerIds']) || is_null($serachCriteria['pro.manufacturerIds'])) ? array() : $serachCriteria['pro.manufacturerIds'];
	            $productStatusIds = (!isset($serachCriteria['pro.productStatusIds']) || is_null($serachCriteria['pro.productStatusIds'])) ? array() : $serachCriteria['pro.productStatusIds'];
	            $objects = Product::getProducts(trim($serachCriteria['pro.sku']), trim($serachCriteria['pro.name']), $supplierIds, $manufacturerIds, $categoryIds, $productStatusIds, trim($serachCriteria['pro.active']), $pageNo, $pageSize, array('pro.name' => 'asc'), $stats);
            }
            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
                $results['items'][] = $obj->getJson();
        }
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() ;
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
    /**
     * Getting price matching information
     *
     * @param unknown $sender
     * @param unknown $param
     */
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
    	}
    	catch(Exception $ex)
    	{
    		$errors[] = $ex->getMessage();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
    /**
     * toggleActive
     *
     * @param unknown $sender
     * @param unknown $param
     */
    public function toggleActive($sender, $param)
    {
    	$results = $errors = array();
    	try
    	{
    		Dao::beginTransaction();
    		$id = isset($param->CallbackParameter->productId) ? $param->CallbackParameter->productId : '';
    		if(!($product = Product::get($id)) instanceof Product)
    			throw new Exception('Invalid product!');
    		$product->setActive(intval($param->CallbackParameter->active))
    			->save();
    		$results['item'] = $product->getJson();
    		Dao::commitTransaction();
    	}
    	catch(Exception $ex)
    	{
    		Dao::rollbackTransaction();
    		$errors[] = $ex->getMessage();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
    /**
     * updateproduct price
     *
     * @param unknown $sender
     * @param unknown $param
     */
    public function updatePrice($sender, $param)
    {
    	$results = $errors = array();
    	try
    	{
    		Dao::beginTransaction();
    		$id = isset($param->CallbackParameter->productId) ? $param->CallbackParameter->productId : '';
    		if(!($product = Product::get($id)) instanceof Product)
    			throw new Exception('Invalid product!');
    		if(!isset($param->CallbackParameter->newPrice))
    			throw new Exception('No New Price Provided!');
    		$newPrice = StringUtilsAbstract::getValueFromCurrency(trim($param->CallbackParameter->newPrice));
    		$prices = ProductPrice::getAllByCriteria('productId = ? and typeId = ?', array($product->getId(), ProductPriceType::ID_RRP), true, 1, 1);
    		if(count($prices) > 0) {
    			$msg = 'Update price for product(SKU=' . $product->getSku() . ') to '. StringUtilsAbstract::getCurrency($newPrice);
    			$price = $prices[0];
    		} else {
    			$msg = 'New Price Created for product(SKU=' . $product->getSku() . '): '. StringUtilsAbstract::getCurrency($newPrice);
    			$price = new ProductPrice();
    			$price->setProduct($product)
    				->setType(ProductPriceType::get(ProductPriceType::ID_RRP));
    		}

    		$price->setPrice($newPrice)
	    		->save()
	    		->addComment($msg, Comments::TYPE_NORMAL)
	    		->addLog($msg, Log::TYPE_SYSTEM);
    		$product->addComment($msg, Log::TYPE_SYSTEM)
	    		->addLog($msg, Log::TYPE_SYSTEM);
    		$results['item'] = $product->getJson();
    		Dao::commitTransaction();
    	}
    	catch(Exception $ex)
    	{
    		Dao::rollbackTransaction();
    		$errors[] = $ex->getMessage() . $ex->getTraceAsString();
    	}
    	$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
}
?>
