<?php
class CatelogConnector extends B2BConnector
{
	public function getProductList()
	{
		return $this->_connect()->catalogProductList ($this->_session);
	}
	/**
	 * Getting information for the product
	 *
	 * @param string $sku The product sku
	 *
	 * @return array
	 */
	public function getProductInfo($sku)
	{
		return $this->_connect()->catalogProductInfo($this->_session, $sku);
	}
	/**
	 * Getting the product category tree
	 * 
	 * @param int $mageCategoryId The magento category id
	 * 
	 * @return array
	 */
	public function getCategoryTree($mageCategoryId = '')
	{
		if(($mageCategoryId = trim($mageCategoryId)) !== '')
			return $this->_connect()->catalogCategoryTree($this->_session, $mageCategoryId);
		return $this->_connect()->catalogCategoryTree($this->_session);
	}
	/**
	 * Getting the categories for the same level
	 * 
	 * @param int $mageCategoryId The magento category id
	 * 
	 * @return array
	 */
	public function getCategoryLevel($mageCategoryId = '')
	{
		if(($mageCategoryId = trim($mageCategoryId)) !== '')
			return $this->_connect()->catalogCategoryLevel($this->_session, null, null, $mageCategoryId);
		return $this->_connect()->catalogCategoryLevel($this->_session);
	}
	/**
	 * Getting the detailed information for a category
	 * 
	 * @param int $mageCategoryId The magento category id
	 * 
	 * @return array
	 */
	public function catalogCategoryInfo($mageCategoryId)
	{
		return $this->_connect()->catalogCategoryInfo($this->_session, $mageCategoryId);
	}
	/**
	 * Importing the category
	 * 
	 * @param string $categoryId
	 * 
	 * @return void|CatelogConnector
	 */
	public function importProductCategories($categoryId = '')
	{
		$categories = $this->getCategoryLevel($categoryId);
		Log::logging(0, get_class($this), 'getting ProductCategories(mageId=' . $categoryId . ')', self::LOG_TYPE, '', __FUNCTION__);
		if(count($categories) === 0)
			return;
		
		foreach($categories as $category)
		{
			try
			{
				Dao::beginTransaction();
				
				$mageId = trim($category->category_id);
				Log::logging(0, get_class($this), 'getting ProductCategory(mageId=' . $mageId . ')', self::LOG_TYPE, '', __FUNCTION__);
				
				$productCategory = ProductCategory::getByMageId($mageId);
				$category = $connector->catalogCategoryInfo($mageId);
				$description = isset($category->description) ? trim($category->description) : trim($category->name);
				if(!$productCategory instanceof ProductCategory)
				{
					Log::logging(0, get_class($this), 'found empty category(mageId=' . $mageId . ')', self::LOG_TYPE, '', __FUNCTION__);
					$productCategory = ProductCategory::create(trim($category->name), $description, ProductCategory::getByMageId(trim($category->parent_id)), true, $mageId);
				}
				else
				{
					Log::logging(0, get_class($this), 'found category(mageId=' . $mageId . ', ID=' . $productCategory->getId() . ')', self::LOG_TYPE, '', __FUNCTION__);
					$productCategory->setName(trim($category->name))
						->setDescription($description)
						->setParent(ProductCategory::getByMageId(trim($category->parent_id)));
				}
				$productCategory->setActive(trim($category->is_active) === '1')
					->setIncludeInMenu(isset($category->include_in_menu) && trim($category->include_in_menu) === '1')
					->setIsAnchor(trim($category->is_anchor) === '1')
					->setUrlKey(trim($category->url_key))
					->save();
				
				Dao::commitTransaction();
				$this->importProductCategories(trim($category->category_id));
			}
			catch(Exception $e)
			{
				Dao::rollbackTransaction();
				throw $ex;
			}
		}
		return $this;
	}
	/**
	 * import all products
	 * 
	 * @return CatelogConnector
	 */
	public function importProducts()
	{
		$products = $this->getProductList();
		foreach($products as $pro)
		{
			try
			{
				Dao::beginTransaction();
				$sku = trim($pro->sku);
				$pro = $this->getProductInfo($sku);
				$name = trim($pro->name);
				$short_description = trim($pro->short_description);
				$description = trim($pro->description);
				$weight = trim($pro->weight);
				$statusId = trim($pro->status);
				$mageId = trim($pro->product_id);
				$price = trim($pro->price);
				$specialPrice = isset($pro->special_price) ? trim($pro->special_price) : '';
				$specialPrice_From = isset($pro->special_from_date) ? trim($pro->special_from_date) : null;
				$specialPrice_To = isset($pro->special_to_date) ? trim($pro->special_to_date) : null;
			
				if(!($product = Product::getBySku($sku)) instanceof Product)
					$product = Product::create($sku, $name);
				$asset = (($assetId = trim($product->getFullDescAssetId())) === '' || !($asset = Asset::getAsset($assetId)) instanceof Asset) ? Asset::registerAsset('full_desc_' . $sku, $description) : $asset;
				$product->setName($name)
					->setMageId($mageId)
					->setShortDescription($short_description)
					->setFullDescAssetId(trim($asset->getAssetId()))
					->setIsFromB2B(true)
					->setStatus(ProductStatus::get($statusId))
					->save()
					->clearAllPrice()
					->addPrice(ProductPriceType::get(ProductPriceType::ID_RRP), $price)
					->addInfo(ProductInfoType::ID_WEIGHT, $weight);
				if($specialPrice !== '')
					$product->addPrice(ProductPriceType::get(ProductPriceType::ID_CASUAL_SPECIAL), $specialPrice, $specialPrice_From, $specialPrice_To);
				if(isset($pro->category_ids) && count($pro->category_ids) > 0)
				{
					$product->clearAllCategory();
					foreach($pro->category_ids as $cateMageId)
					{
						if(!($category = ProductCategory::getByMageId($cateMageId)) instanceof ProductCategory)
							continue;
						$product->addCategory($category);
					}
				}
				Dao::commitTransaction();
				die;
			}
			catch(Exception $ex)
			{
				Dao::rollbackTransaction();
				throw $ex;
			}
		}
		return $this;
	}
}