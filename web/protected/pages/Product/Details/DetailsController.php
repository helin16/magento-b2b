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
	public $menuItem = 'product.details';
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
		$locationTypes = array_map(create_function('$a', 'return $a->getJson();'), PreferredLocationType::getAll());
		
		$js .= "pageJs.setPreData(" . json_encode($manufacturers) . ", " . json_encode($suppliers) . ", " . json_encode($statuses) . ", " . json_encode($priceTypes) . ", " . json_encode($codeTypes) . ", " . json_encode($locationTypes) . ")";
		$js .= ".setCallbackId('getCategories', '" . $this->getCategoriesBtn->getUniqueID() . "')";
		$js .= ".load()";
		$js .= ".bindAllEventNObjects();";
		return $js;
	}
	/**
	 * Getting the categories
	 *
	 * @param unknown $sender
	 * @param unknown $param
	 * @throws Exception
	 *
	 */
	public function getCategories($sender, $param)
	{
		$results = $errors = array();
		try
		{
			$categories = array();
			foreach(ProductCategory::getAllByCriteria('parentId is null', array()) as $category)
			{
				$categories[] = $this->_getCategoryJson($category);
			}
			$results['items'] = $categories;
		}
		catch(Exception $ex)
		{
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
	/**
	 * Getting the json for a product category
	 * 
	 * @param ProductCategory $category
	 * 
	 * @return multitype:multitype:NULL
	 */
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
	private function _updateFullDescription(Product &$product, $param)
	{
		//update full description
		if(isset($param->CallbackParameter->fullDescription) && ($fullDescription = trim($param->CallbackParameter->fullDescription)) !== '')
		{
			if(($fullAsset = Asset::getAsset($product->getFullDescAssetId())) instanceof Asset)
				Asset::removeAssets(array($fullAsset->getAssetId()));
			$fullAsset = Asset::registerAsset('full_description_for_product.txt', $fullDescription);
			$product->setFullDescAssetId($fullAsset->getAssetId());
		}
		return $this;
	}
	private function _updateCategories(Product &$product, $param)
	{
		//update categories
		if(isset($param->CallbackParameter->categoryIds) && count($categoryIds = $param->CallbackParameter->categoryIds) > 0)
		{
			Product_Category::deleteByCriteria('productId = ?', array(trim($product->getId())));
			foreach($categoryIds as $categoryId)
			{
				if(!($category = ProductCategory::get($categoryId)))
					continue;
				Product_Category::create($product, $category);
			}
		}
		return $this;
	}
	private function _uploadImages(Product &$product, $param)
	{
		//upload images
		if(isset($param->CallbackParameter->images) && count($images = $param->CallbackParameter->images) > 0)
		{
			foreach($images as $image)
			{
				if(($assetId = trim($image->imageAssetId)) === '')
				{
					if($image->active === true)
					{
						$data = explode(',', $image->data);
						$asset = Asset::registerAsset(trim($image->filename), base64_decode($data[1]));
						ProductImage::create($product, $asset);
					}
					//if it's deactivated one, ignore
				}
				else if (!($asset = Asset::getAsset($assetId)) instanceof Asset)
					continue;
					
				if($image->active === false) {
					ProductImage::remove($product, $asset);
				}
			}
		}
		return $this;
	}
	private function _setPrices(Product &$product, $param)
	{
		if(isset($param->CallbackParameter->prices) && count($prices = $param->CallbackParameter->prices) > 0)
		{
			//delete all price first
			$deleteIds = array();
			foreach($prices as $price)
			{
				if(trim($price->active) === '0' && isset($price->id))
					$deleteIds[] = trim($price->id);
			}
			if(count($deleteIds) > 0)
				ProductPrice::updateByCriteria('active = 0', 'id in (' . str_repeat('?', count($deleteIds)) . ')', $deleteIds);
			//update or create new
			foreach($prices as $price)
			{
				if(isset($price->id) && in_array(trim($price->id), $deleteIds))
					continue;
				if(!($type = ProductPriceType::get(trim($price->typeId))) instanceof ProductPriceType)
					continue;
				$priceValue = trim($price->value);
				$start = trim($price->start);
				$end = trim($price->end);
				if(!is_numeric(StringUtilsAbstract::getValueFromCurrency($priceValue)))
					throw new Exception('Invalid price: ' . $priceValue);
				
				if(!isset($price->id) || ($id = trim($price->id)) === '')
				{
					if(trim($price->active) === '1')
						ProductPrice::create($product, $type, $priceValue, $start, $end);
					//if it's deactivated one, ignore
				}
				else if (($productPrice = ProductPrice::get($id)) instanceof ProductPrice)
				{
					$productPrice
						->setPrice($priceValue)
						->setType($type)
						->setProduct($product)
						->setStart($start)
						->setEnd($end)
						->save();
				}
					
			}
		}
		return $this;
	}
	private function _setLocation(Product &$product, $param)
	{
		if(isset($param->CallbackParameter->locations) && count($locations = $param->CallbackParameter->locations) > 0)
		{
			//delete all price first
			$deleteIds = array();
			foreach($locations as $location)
			{
				if(trim($location->active) === '0' && isset($location->id))
					$deleteIds[] = trim($location->id);
			}
			if(count($deleteIds) > 0)
				PreferredLocation::updateByCriteria('active = 0', 'id in (' . str_repeat('?', count($deleteIds)) . ')', $deleteIds);
			
			//update or create new
			foreach($locations as $location)
			{
				if(isset($location->id) && in_array(trim($location->id), $deleteIds))
					continue;
				if(!($type = PreferredLocationType::get(trim($location->typeId))) instanceof PreferredLocationType)
					continue;
				
				$locationName = trim($location->value);
				$locs = Location::getAllByCriteria('name = ?', array($locationName), true, 1, 1);
				$loc = (count($locs) > 0 ? $locs[0] : Location::create($locationName, $locationName));
				if(!isset($location->id) || ($id = trim($location->id)) === '')
				{
					if(trim($location->active) === '1')
						PreferredLocation::create($loc, $product, $type);
					//if it's deactivated one, ignore
				}
				else if (($preferredLocation= PreferredLocation::get($id)) instanceof PreferredLocation)
				{
					$preferredLocation->setLocation($loc)
						->setActive(trim($location->active) === '1')
						->setProduct($product)
						->setType($type)
						->save();
				}
			}
		}
		return $this;
	}
	private function _setBarcodes(Product &$product, $param)
	{
		if(isset($param->CallbackParameter->productCodes) && count($productCodes = $param->CallbackParameter->productCodes) > 0)
		{
			foreach($productCodes as $code)
			{
				if(!($type = ProductCodeType::get(trim($code->typeId))) instanceof ProductCodeType)
					continue;
				
				if(!isset($code->id) || ($id = trim($code->id)) === '')
				{
					if(trim($code->active) === '1')
						ProductCode::create($product, $type, trim($code->value));
					//if it's deactivated one, ignore
				}
				else if (($productCode = ProductCode::get($id)) instanceof ProductCode)
				{
					$productCode->setActive(trim($code->active) === '1')
						->setCode(trim($code->value))
						->setType($type)
						->setProduct($product)
						->save();
				}
			}
		}
		return $this;
	}
	private function _setSupplierCodes(Product &$product, $param)
	{
		if(isset($param->CallbackParameter->supplierCodes) && count($supplierCodes = $param->CallbackParameter->supplierCodes) > 0)
		{
			foreach($supplierCodes as $code)
			{
				if(!($supplier = Supplier::get(trim($code->typeId))) instanceof Supplier)
					continue;
				if(!isset($code->id) || ($id = trim($code->id)) === '')
				{
					if(trim($code->active) === '1')
						SupplierCode::create($product, $supplier, trim($code->value));
					//if it's deactivated one, ignore
				}
				else if (($supplierCode = SupplierCode::get($id)) instanceof SupplierCode)
				{
					$supplierCode->setActive(trim($code->active) === '1')
						->setCode(trim($code->value))
						->setSupplier($supplier)
						->setProduct($product)
						->save();
				}
			}
		}
		return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see DetailsPageAbstract::saveItem()
	 */
	public function saveItem($sender, $param)
	{
		$results = $errors = array();
		try
		{
			Dao::beginTransaction();
			$product = !isset($param->CallbackParameter->id) ? new Product() : Product::get(trim($param->CallbackParameter->id));
			if(!$product instanceof Product)
				throw new Exception('Invalid Product passed in!');
			if (!($manufacturer = Manufacturer::get(trim($param->CallbackParameter->manufacturerId))) instanceof Manufacturer)
				throw new Exception('Invalid Manufacturer/Brand!');
			if (!($status = ProductStatus::get(trim($param->CallbackParameter->statusId))) instanceof ProductStatus)
				throw new Exception('Invalid Status!');
			$sku = trim($param->CallbackParameter->sku);
			$name = trim($param->CallbackParameter->name);
			$shortDescription = trim($param->CallbackParameter->shortDescription);
			$sellOnWeb = (trim($param->CallbackParameter->sellOnWeb) === '1');
			
			$product->setName($name)
				->setSku($sku)
				->setShortDescription($shortDescription)
				->setStatus($status)
				->setManufacturer($manufacturer)
				->setSellOnWeb($sellOnWeb)
				->setAsNewFromDate(trim($param->CallbackParameter->asNewFromDate))
				->setAsNewToDate(trim($param->CallbackParameter->asNewToDate))
				->setInvenAccNo(trim($param->CallbackParameter->invenAccNo))
				;
			if(trim($product->getId()) === '')
				$product->setIsFromB2B(false);
			$product->save();
			
			$this->_updateFullDescription($product, $param)
				->_updateCategories($product, $param)
				->_uploadImages($product, $param)
				->_setSupplierCodes($product, $param)
				->_setBarcodes($product, $param)
				->_setPrices($product, $param)
				->_setLocation($product, $param);
			
			$product->save();
			$results['url'] = '/product/' . $product->getId() . '.html';
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
