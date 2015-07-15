<?php
class CatelogConnector extends B2BConnector
{
	public function getProductList($setFromDate = false)
	{
		$date = $setFromDate === true ? SystemSettings::getSettings(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL) : '';
		echo 'Looking for Magento Products with Create Date From: "' . $date . '"' . "\n";
		$params = array('complex_filter'=>
				array(
						array('key'=>'created_at','value'=>array('key' =>'from','value' => $date))
				)
		);
		return $this->_connect()->catalogProductList ($this->_session, $params);
	}
	/**
	 * Getting the attribute information
	 *
	 * @param unknown $mageAttrId
	 *
	 * @return array
	 */
	public function getProductAttributeOptions($mageAttrId)
	{
		return $this->_connect()->catalogProductAttributeOptions($this->_session, $mageAttrId);
	}
	/**
	 * Getting the product attributes list
	 *
	 * @param int $mageSetId
	 *
	 * @return array
	 */
	public function getProductAttributeList($mageSetId)
	{
		return $this->_connect()->catalogProductAttributeList($this->_session, $mageSetId);
	}
	/**
	 * Getting information for the product
	 *
	 * @param string $sku The product sku
	 *
	 * @return array
	 */
	public function getProductInfo($sku, $attributes = array())
	{
		return $this->_connect()->catalogProductInfo($this->_session, $sku, null, $attributes);
	}
	/**
	 * update product price on magento
	 * 
	 * @param string $sku
	 * @param double $price
	 * @throws Exception
	 * @return Ambigous <boolean, NULL>
	 */
	public function updateProductPrice($sku, $price)
	{
		if(trim($price) === '' || doubleval(trim($price)) <= doubleval(0))
			throw new Exception('invalid price passed in. "' . $price . '" given.');
		$price = doubleval(trim($price));
		return $this->updateProductInfo($sku, array('price'=> $price));
	}
	/**
	 * update the required product on magento, only the given attributes
	 * 
	 * @param string $sku
	 * @param array $params
	 * @throws Exception
	 * @return bool | string | null
	 */
	public function updateProductInfo($sku, $params = array())
	{
		if(trim($sku) === '')
			throw new Exception('Invalid sku passed in. "' . $sku .'" given.');
		$sku = trim($sku);
		$currentInfo = $this->getProductInfo($sku);
		$newinfo = array();
		$result = null;
		
		if(count($params) > 0 && $this->getProductInfo($sku) !== null)
		{
			if(isset($params['categories']))
				$newinfo['categories'] = $params['categories'];
			if(isset($params['websites']))
				$newinfo['websites'] = $params['websites'];
			if(isset($params['name']))
				$newinfo['name'] = $params['name'];
			if(isset($params['description']))
				$newinfo['description'] = $params['description'];
			if(isset($params['short_description']))
				$newinfo['short_description'] = $params['short_description'];
			if(isset($params['weight']))
				$newinfo['weight'] = $params['weight'];
			if(isset($params['status']))
				$newinfo['status'] = $params['status'];
			if(isset($params['url_key']))
				$newinfo['url_key'] = $params['url_key'];
			if(isset($params['url_path']))
				$newinfo['url_path'] = $params['url_path'];
			if(isset($params['visibility']))
				$newinfo['visibility'] = $params['visibility'];
			if(isset($params['price']))
				$newinfo['price'] = $params['price'];
			if(isset($params['tax_class_id']))
				$newinfo['tax_class_id'] = $params['tax_class_id'];
			if(isset($params['meta_title']))
				$newinfo['meta_title'] = $params['meta_title'];
			if(isset($params['meta_keyword']))
				$newinfo['meta_keyword'] = $params['meta_keyword'];
			if(isset($params['meta_description']))
				$newinfo['meta_description'] = $params['meta_description'];
			
			if(count($newinfo) > 0)
			{
				$result = $this->_connect()->catalogProductUpdate($this->_session, $sku, $newinfo);
				if($result !== true)
					throw new Exception('Product not updated. Message from Magento: "' . $result . '"');
			}
		}
		
		return $result;
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
				$category = $this->catalogCategoryInfo($mageId);
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
	public function getInfoAttributes()
	{
		$attributeName = array('name', 'manufacturer', 'man_code', 'news_from_date', 'news_to_date', 'price', 'short_description', 'supplier', 'description', 'weight', 'status', 'special_price', 'special_from_date', 'special_to_date');
		$attributes = new stdclass();
		$attributes->additional_attributes = $attributeName;
		return $attributes;
	}
	public function getManufacturerName($mageManuValue)
	{
		$options = $this->getProductAttributeOptions('manufacturer');
		if(count($options) === 0)
			return;

		foreach($options as $option)
		{
			if(trim($option->value) === trim($mageManuValue))
				return Manufacturer::create(trim($option->label), trim($option->label), true, trim($mageManuValue));
		}
		throw new Exception('No manufacture found with value(=' . $mageManuValue . '!');
	}
	private function _getAttributeFromAdditionAttr($attrArray)
	{
		$array = array();
		foreach($attrArray as $attr)
			$array[trim($attr->key)] = trim($attr->value);
		return $array;
	}
	/**
	 * import all products
	 *
	 * @return CatelogConnector
	 */
	public function importProducts($setFromDate = false, $newOnly = false)
	{
		$products = $this->getProductList($setFromDate);
		foreach($products as $pro)
		{
			$transStarted = false;
			try
			{
				try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
				
				$mageId = trim($pro->product_id);
				$sku = trim($pro->sku);
				$pro = $this->getProductInfo($sku, $this->getInfoAttributes());
				if(is_null($pro) || !isset($pro->additional_attributes))
					continue;
				if($newOnly === true && ($product = Product::getBySku($sku)) instanceof Product)
				{
					echo 'Product ' . $sku . '(id=' . $product->getId() . ') already exist, skiped' . "\n";
					continue;
				}

				$additionAttrs = $this->_getAttributeFromAdditionAttr($pro->additional_attributes);
				$name = trim($additionAttrs['name']);
				$short_description = trim($additionAttrs['short_description']);
				$description = trim($additionAttrs['description']);
				$weight = trim($additionAttrs['weight']);
				$statusId = trim($additionAttrs['status']);
				$price = trim($additionAttrs['price']);
				$specialPrice = isset($additionAttrs['special_price']) ? trim($additionAttrs['special_price']) : '';
				$specialPrice_From = isset($additionAttrs['special_from_date']) ? trim($additionAttrs['special_from_date']) : null;
				$specialPrice_To = isset($additionAttrs['special_to_date']) ? trim($additionAttrs['special_to_date']) : null;

				if(!($product = Product::getBySku($sku)) instanceof Product)
				{
					$product = Product::create($sku, $name);
					Log::logging(0, get_class($this), 'Found New Product from Magento with sku="' . trim($sku) . '" and name="' . $name . '"', self::LOG_TYPE, '', __FUNCTION__);
					echo 'Found New Product from Magento with sku="' . $sku . '", name="' . $name . '"' . "\n";
				}
				$asset = (($assetId = trim($product->getFullDescAssetId())) === '' || !($asset = Asset::getAsset($assetId)) instanceof Asset) ? Asset::registerAsset('full_desc_' . $sku, $description, Asset::TYPE_PRODUCT_DEC) : $asset;
				$product->setName($name)
					->setMageId($mageId)
					->setShortDescription($short_description)
					->setFullDescAssetId(trim($asset->getAssetId()))
					->setIsFromB2B(true)
					->setStatus(ProductStatus::get($statusId))
					->setSellOnWeb(true)
					->setManufacturer($this->getManufacturerName(trim($additionAttrs['manufacturer'])))
					->save()
					->clearAllPrice()
					->addPrice(ProductPriceType::get(ProductPriceType::ID_RRP), $price)
					->addInfo(ProductInfoType::ID_WEIGHT, $weight);

				if($specialPrice !== '')
					$product->addPrice(ProductPriceType::get(ProductPriceType::ID_CASUAL_SPECIAL), $specialPrice, $specialPrice_From, $specialPrice_To);

				if(isset($additionAttrs['supplier']) && ($supplierName = trim($additionAttrs['supplier'])) !== '')
					$product->addSupplier(Supplier::create($supplierName, $supplierName, true));

				if(isset($pro->categories) && count($pro->categories) > 0)
				{
					$product->clearAllCategory();
					foreach($pro->category_ids as $cateMageId)
					{
						if(!($category = ProductCategory::getByMageId($cateMageId)) instanceof ProductCategory)
							continue;
						$product->addCategory($category);
					}
				}
				
				if($transStarted === false)
					Dao::commitTransaction();
			}
			catch(Exception $ex)
			{
				if($transStarted === false)
					Dao::rollbackTransaction();
			}
		}
		return $this;
	}
}