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
		$js .= 'pageJs.totalValueId = "total-found-value";';
		$js .= 'pageJs.totalQtyId = "total-found-qty";';
		$js .= 'pageJs._loadManufactures('.json_encode($manufactureArray).')';
		$js .= '._loadSuppliers('.json_encode($supplierArray).')';
		$js .= '._loadCategories('.json_encode($productCategoryArray).')';
		$js .= '._loadProductStatuses('.json_encode($statuses).')';
		$js .= "._loadChosen()";
		$js .= "._bindSearchKey()";
		$js .= "._bindNewRuleBtn()";
		$js .= ".setCallbackId('priceMatching', '" . $this->priceMatchingBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('toggleActive', '" . $this->toggleActiveBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('updatePrice', '" . $this->updatePriceBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('updateStockLevel', '" . $this->updateStockLevelBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('toggleIsKit', '" . $this->toggleIsKitBtn->getUniqueID() . "')";
		$js .= ".setCallbackId('newRule', '" . $this->newRuleBtn->getUniqueID() . "')";
		$js .= ".getResults(true, " . $this->pageSize . ");";
		return $js;
	}
	public function newRule($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			
			$results = $param->CallbackParameter;
			
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
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
            $sumArray = array();
            if(isset($serachCriteria['pro.id']) && ($product = Product::get($serachCriteria['pro.id'])) instanceof Product) {
            	$objects = array($product);
            	$stats = array('totalPages' => 1);
            } else {
	            $pageNo = 1;
	            $pageSize = DaoQuery::DEFAUTL_PAGE_SIZE;

	            if(isset($param->CallbackParameter->pagination))
	            {
	                $pageNo = $param->CallbackParameter->pagination->pageNo;
	                $pageSize = $param->CallbackParameter->pagination->pageSize;
	            }

	            $stats = array();
	            
	            $serachCriteria = $this->getSearchCriteria($serachCriteria);
	            
	            $objects = Product::getProducts(
	            		$serachCriteria->sku
	            		,$serachCriteria->name
	            		,$serachCriteria->supplierIds
	            		,$serachCriteria->manufacturerIds
	            		,$serachCriteria->categoryIds
	            		,$serachCriteria->productStatusIds
	            		,$serachCriteria->active
	            		,$pageNo
	            		,$pageSize
	            		,array('pro.name' => 'asc')
	            		,$stats
	            		,$serachCriteria->stockLevel
	            		,$sumArray
	            		,$serachCriteria->sh_from
	            		,$serachCriteria->sh_to
	            		);
            }
            $results['pageStats'] = $stats;
            $results['items'] = array();
            foreach($objects as $obj)
                $results['items'][] = $obj->getJson();
            $results['totalStockOnHand'] = isset($sumArray['totalStockOnHand']) ? trim($sumArray['totalStockOnHand']) : 0;
            $results['totalOnHandValue'] = isset($sumArray['totalOnHandValue']) ? trim($sumArray['totalOnHandValue']) : 0;
        }
        catch(Exception $ex)
        {
            $errors[] = $ex->getMessage() ;
        }
        $param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
    }
    private function getSearchCriteria($serachCriteria)
    {
    	$result = new stdClass();
    	//sku
        $sku = trim($serachCriteria['pro.sku']);
        if(strpos($sku, ',') !== false) {
        	$sku = array_map(create_function('$a', 'return trim($a);'), explode(',', $sku));
        }
        $result->sku = $sku;
        //name
        $result->name = trim($serachCriteria['pro.name']);
        //suppliers
        if(!isset($serachCriteria['pro.supplierIds']) || is_null($serachCriteria['pro.supplierIds']))
        	$result->supplierIds = array();
        else $result->supplierIds = $serachCriteria['pro.supplierIds'];
        //manufactures
        if(!isset($serachCriteria['pro.manufacturerIds']) || is_null($serachCriteria['pro.manufacturerIds']))
        	$result->manufacturerIds = array();
        else $result->manufacturerIds = $serachCriteria['pro.manufacturerIds'];
        //categories
        if(!isset($serachCriteria['pro.productCategoryIds']) || is_null($serachCriteria['pro.productCategoryIds']))
        	$result->categoryIds = array();
        else $result->categoryIds = $serachCriteria['pro.productCategoryIds'];
        //product statuses
        if(!isset($serachCriteria['pro.productStatusIds']) || is_null($serachCriteria['pro.productStatusIds']))
        	$result->productStatusIds = array();
        else $result->productStatusIds = $serachCriteria['pro.productStatusIds'];
        //product active
        if(trim($serachCriteria['pro.active']) === "ALL")
        	$result->active = " ";
        else $result->active = trim($serachCriteria['pro.active']);
        //stock on hand from
        if(is_array(json_decode($serachCriteria['pro.sh'])) && count(json_decode($serachCriteria['pro.sh'])) === 2)
        {
        	$result->sh_from = json_decode($serachCriteria['pro.sh'])[0];
        	$result->sh_to = json_decode($serachCriteria['pro.sh'])[1];
        }
        else throw new Exception('Invalid stock on hand range, "' . $serachCriteria['pro.sh'] . '" given');
        //stock level
        $result->stockLevel = $serachCriteria['pro.stockLevel'];
        
        return $result;
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
     * toggleIsKit
     *
     * @param unknown $sender
     * @param unknown $param
     */
    public function toggleIsKit($sender, $param)
    {
    	$results = $errors = array();
    	try
    	{
    		Dao::beginTransaction();
    		$id = isset($param->CallbackParameter->productId) ? $param->CallbackParameter->productId : '';
    		if(!($product = Product::get($id)) instanceof Product)
    			throw new Exception('Invalid product!');
    		$product->setIsKit(intval($param->CallbackParameter->isKit))
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
    public function updateStockLevel($sender, $param)
    {
    	$results = $errors = array();
    	try
    	{
    		Dao::beginTransaction();
    		$id = isset($param->CallbackParameter->productId) ? $param->CallbackParameter->productId : '';
    		if(!($product = Product::get($id)) instanceof Product)
    			throw new Exception('Invalid product!');
    		if(!isset($param->CallbackParameter->newValue))
    			throw new Exception('No New ' . $param->CallbackParameter->type .' Provided!');
    		else $newValue = intval($param->CallbackParameter->newValue);
    		if(!isset($param->CallbackParameter->type))
    			throw new Exception('Invalue Type "' . $param->CallbackParameter->type . '" Provided!');
    		else $type = $param->CallbackParameter->type;
    		switch($param->CallbackParameter->type)
    		{
    			case 'stockMinLevel':
    				$msg = 'Update ' . $type .' for product(SKU=' . $product->getSku() . ') to '. $param->CallbackParameter->newValue;
    				$product->setStockMinLevel($newValue)
	    				->addComment($msg, Comments::TYPE_NORMAL)
	    				->addLog($msg, Log::TYPE_SYSTEM);
    				break;
    			case 'stockReorderLevel':
    				$msg = 'Update ' . $type .' for product(SKU=' . $product->getSku() . ') to '. $param->CallbackParameter->newValue;
    				$product->setStockReorderLevel($newValue)
	    				->addComment($msg, Comments::TYPE_NORMAL)
	    				->addLog($msg, Log::TYPE_SYSTEM);
    				break;
    			default: throw new Exception('Invalue Type "' . $param->CallbackParameter->type . '" Provided!');
    		}
    		$product->save();
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
