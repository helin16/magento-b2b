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
		
		$js .= "pageJs.setPreData(" . json_encode($manufacturers) . ", " . json_encode($suppliers) . ", " . json_encode($statuses) . ", " . json_encode($priceTypes) . ", " . json_encode($codeTypes) . ")";
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
			var_dump($param->CallbackParameter);
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
				->setSellOnWeb($sellOnWeb);
			if(trim($product->getId()) === '')
				$product->setIsFromB2B(false);
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
			
			$product->save();
			Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			Dao::rollbackTransaction();
			$errors[] = $ex->getMessage();
		}
		$param->ResponseData = StringUtilsAbstract::getJson($results, $errors);
	}
}
?>
