<?php
class CatelogConnector extends B2BConnector
{
	public function getProductList($fromDate)
	{
		$fromDate = trim($fromDate);
// 		$fromId = trim($fromId);
		$array = array();
// 		if($fromId !== '')
// 		{
// 			$array[] = array('key'=>'product_id','value'=>array('key' =>'gteq','value' => intval($fromId)));
// 			$array[] = array('key'=>'product_id','value'=>array('key' =>'lteq','value' => (intval($fromId) + $idStepSize) ));
// 			echo 'Looking for Magento Products with ID From: "' . intval($fromId) . '" To: "' . (intval($fromId) + $idStepSize) . '"' . "\n";
// 		}
// 		if($fromDate !== '')
// 		{
			$array[] = array('key'=>'created_at','value'=>array('key' =>'from','value' => trim($fromDate)));
// 			echo 'Looking for Magento Products with Date From: "' . $fromDate . '"' . "\n";
// 		}
		if(count($array) === 0)
			throw new Exception('no param given');
		$params = array('complex_filter' => $array);
		return $this->_connect()->catalogProductList($this->_session, $params);
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
	 * Getting the product attributes set list
	 *
	 * @return array
	 */
	public function getProductAttributeSetList()
	{
		return $this->_connect()->catalogProductAttributeSetList($this->_session);
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
		$attributes = ($attributes === array() ? $this->getInfoAttributes() : $attributes);
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
			if(isset($params['status']))
				$newinfo['status'] = $params['status'];
			if(isset($params['sku']))
				$newinfo['sku'] = $params['sku'];
			if(count($newinfo) > 0)
			{
				try {
					$result = $this->_connect()->catalogProductUpdate($this->_session, $sku, $newinfo);
				} catch (SoapFault $e) {
					return '***warning*** push Product "' . $sku . '" info to magento faled. Message from Magento: "' . $e -> getMessage() . '"' . "\n";
				}
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
		
		try {
			$transStarted = false;
			try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
			foreach($categories as $category)
			{
				$mageId = trim($category->category_id);
				Log::logging(0, get_class($this), 'getting ProductCategory(mageId=' . $mageId . ')', self::LOG_TYPE, '', __FUNCTION__);

				$productCategory = ProductCategory::getByMageId($mageId);
				$category = $this->catalogCategoryInfo($mageId);
				if(!is_object($category))
					continue;
				$description = isset($category->description) ? trim($category->description) : trim($category->name);
				if(!$productCategory instanceof ProductCategory)
				{
					Log::logging(0, get_class($this), 'found new category from magento(mageId=' . $mageId . ', name="' . $category->name . '"' . ')', self::LOG_TYPE, '', __FUNCTION__);
					echo 'found new category from magento(mageId=' . $mageId . ', name="' . $category->name . '"' . ')' . "\n";
					$productCategory = ProductCategory::create(trim($category->name), $description, ProductCategory::getByMageId(trim($category->parent_id)), true, $mageId);
				}
				else
				{
					Log::logging(0, get_class($this), 'found existing category from magento(mageId=' . $mageId . ', name="' . $category->name . '", ID=' . $productCategory->getId() . ')', self::LOG_TYPE, '', __FUNCTION__);
					echo 'found existing category from magento(mageId=' . $mageId . ', name="' . $category->name . '", ID=' . $productCategory->getId() . ')' . "\n";
					$productCategory->setName(trim($category->name))
						->setDescription($description)
						->setParent(ProductCategory::getByMageId(trim($category->parent_id)));
				}
				$productCategory->setActive(trim($category->is_active) === '1')
					->setIncludeInMenu(isset($category->include_in_menu) && trim($category->include_in_menu) === '1')
					->setIsAnchor(trim($category->is_anchor) === '1')
					->setUrlKey(trim($category->url_key))
					->save();

				$this->importProductCategories(trim($category->category_id));
			}
			if($transStarted === false)
				Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
		if($transStarted === false)
			Dao::rollbackTransaction();
			throw $ex;
		}
		return $this;
	}
	//create($sku, $name, $mageProductId = '', $stockOnHand = null, $stockOnOrder = null, $isFromB2B = false, $shortDescr = '', $fullDescr = '', Manufacturer $manufacturer = null, $assetAccNo = null, $revenueAccNo = null, $costAccNo = null, $stockMinLevel = null, $stockReorderLevel = null)
	public function getInfoAttributes()
	{
		$attributeName = array('name', 'product_id', 'short_description', 'description', 'manufacturer', 'man_code', 'news_from_date', 'news_to_date', 'price', 'supplier', 'weight', 'status', 'special_price', 'special_from_date', 'special_to_date');
		$attributes = new stdclass();
		$attributes->additional_attributes = $attributeName;
		return $attributes;
	}
	/**
	 * 
	 * @param int $mageManuValue
	 * @throws Exception
	 * @return Manufacturer
	 */
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
		
		// if no manufacture assgin to product on magento side, assume unset ('unset' is a proper manufacture option in mangento)
		$manufacturers = Manufacturer::getAllByCriteria('name = ? and isFromB2B = 1 and mageId <> 0', array('unset'), true, 1, 1, array("id"=>"desc"));
		if(count($manufacturers) > 0)
			return $manufacturers[0];
		
		throw new Exception('Invalid manufacture found with value(=' . $mageManuValue . '!');
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
	public function importProducts($setFromDate = false, $newOnly = false, $setFromId = false)
	{
		if($setFromDate === false && $setFromId === false)
			throw new Exception('must give a limitation on product pull');
		if(!($systemSetting = SystemSettings::getByType(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL)) instanceof SystemSettings)
			throw new Exception('cannot set LAST_NEW_PRODUCT_PULL in system setting');
		// from id has higher priority
		if($setFromId === true && !($systemSetting = SystemSettings::getByType(SystemSettings::TYPE_LAST_PRODUCT_PULL_ID)) instanceof SystemSettings)
			throw new Exception('cannot set TYPE_LAST_PRODUCT_PULL_ID in system setting');
		$fromDate = '';
		$fromId = '';
		if($setFromId === true)
			$fromId = $systemSetting->getValue();
		elseif($setFromDate === true)
			$fromDate = $systemSetting->getValue();
		$products = $this->getProductList($fromDate, $fromId);
		if(count($products) === 0)
		{
			echo 'nothing from magento. exitting' . "\n";
			return $this;
		}
		try
		{
			$transStarted = false;
			try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
			foreach($products as $pro)
			{
				$mageId = trim($pro->product_id);
				$sku = trim($pro->sku);
				$pro = $this->getProductInfo($sku, $this->getInfoAttributes());
				$created_at = trim($pro->created_at);
				$product_id = trim($pro->product_id);
				if(is_null($pro) || !isset($pro->additional_attributes))
					continue;
				// handle extra long sku from magento, exceeding mysql sku length limit
				DaoMap::loadMap('Product');
				$skuSizeLimit = DaoMap::$map['product']['sku']['size'];
				if(strlen($sku) > $skuSizeLimit)
				{
					echo 'Product ' . $sku . '(id=' . $product->getId() . ', magento Product Creation Time=' . trim($pro->created_at) . ') magento sku length exceed system sku length limit of' . $skuSizeLimit . ', skipped' . "\n";
					continue;
				}
				if($newOnly === true && ($product = Product::getBySku($sku)) instanceof Product)
				{
					echo 'Product ' . $sku . '(id=' . $product->getId() . ', magento Product Creation Time=' . trim($pro->created_at) . ') already exist, skipped' . "\n";
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
					Log::logging(0, get_class($this), 'Found New Product from Magento with sku="' . trim($sku) . '" and name="' . $name . '", created_at="' . $created_at, self::LOG_TYPE, '', __FUNCTION__);
					echo 'Found New Product from Magento with sku="' . trim($sku) . '" and name="' . $name . '", created_at="' . $created_at . "\n";
				} elseif(Product::getBySku($sku) instanceof Product) // update old product description 
				{
					$product = Product::getBySku($sku);
					echo 'Found Existing Product from Magento with sku="' . trim($sku) . '" and name="' . $name . '", created_at="' . $created_at . '"' . "\n";
					echo "\t" . 'Name: "' . $name . '"' . "\n"; 
					echo "\t" . 'MageId: "' . $mageId . '"' . "\n"; 
					echo "\t" . 'Short Description: "' . $short_description . '"' . "\n"; 
					echo "\t" . 'Full Description: "' . $description . '"' . "\n"; 
					echo "\t" . 'Status: "' . ProductStatus::get($statusId) . '"' . "\n"; 
					echo "\t" . 'Manufacturer: id=' . $this->getManufacturerName(trim($additionAttrs['manufacturer']))->getId() . ', name="' . $this->getManufacturerName(trim($additionAttrs['manufacturer']))->getName() . '"' . "\n"; 
					echo "\t" . 'Price: "' . $price . '"' . "\n"; 
					echo "\t" . 'Weight: "' . $weight . '"' . "\n"; 
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
			}
			if($setFromId === true && ($systemSetting = SystemSettings::getByType(SystemSettings::TYPE_LAST_PRODUCT_PULL_ID)) instanceof SystemSettings)
				$systemSetting->setValue($product_id)->save();
			elseif($setFromDate === true && ($systemSetting = SystemSettings::getByType(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL)) instanceof SystemSettings)
				$systemSetting->setValue($created_at)->save();
			if($transStarted === false)
				Dao::commitTransaction();
		}
		catch(Exception $ex)
		{
			if($transStarted === false)
				Dao::rollbackTransaction();
			throw $ex;
		}
		return $this;
	}
	/**
	 * Importing the attribute sets from magento
	 *
	 * @return void|CatelogConnector
	 */
	public function importProductAttributeSets()
	{
		$attributeSets = $this->getProductAttributeSetList();
		Log::logging(0, get_class($this), 'getting AttributeSets from magento', self::LOG_TYPE, '', __FUNCTION__);
		echo 'getting AttributeSets from magento' . "\n";
		if(count($attributeSets) === 0)
			return;
		try
		{
			$transStarted = false;
			try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
			foreach($attributeSets as $attributeSet)
			{
				$mageId = trim($attributeSet->set_id);
				$name = trim($attributeSet->name);
				$description = isset($category->description) ? trim($category->description) : $name;
				Log::logging(0, get_class($this), 'getting AttributeSet(mageId="' . $mageId . '")', self::LOG_TYPE, '', __FUNCTION__);
				
				$productAttributeSet = ProductAttributeSet::getByMageId($mageId);
				if(!$productAttributeSet instanceof ProductAttributeSet)
				{
					Log::logging(0, get_class($this), 'found new AttributeSet from magento(mageId=' . $mageId . ', name="' . $name . '"' . ')', self::LOG_TYPE, '', __FUNCTION__);
					echo 'found new AttributeSet from magento(mageId=' . $mageId . ', name="' . $name . '"' . ')' . "\n";
					$productAttributeSet = ProductAttributeSet::create($name, $description, true, $mageId);
				}
				else
				{
					Log::logging(0, get_class($this), 'found existing AttributeSet from magento(mageId=' . $mageId . ', name="' .$name . '", ID=' . $productAttributeSet->getId() . ')', self::LOG_TYPE, '', __FUNCTION__);
					echo 'found existing AttributeSet from magento(mageId=' . $mageId . ', name="' . $name . '", ID=' . $productAttributeSet->getId() . ')' . "\n";
					$productAttributeSet->setName($name)
					->setDescription($description);
				}
				$productAttributeSet->setActive(true)
				->save();
			}
			if($transStarted === false)
				Dao::commitTransaction();
		}
		catch(Exception $e)
		{
			Dao::rollbackTransaction();
			throw $e;
		}
		return $this;
	}
	/**
	 * Importing the attributes from magento
	 *
	 * @return void|CatelogConnector
	 */
	public function importProductAttributes()
	{
		$productAttributeSetIds = Dao::getResultsNative('select distinct pro_att_set.id from productattributeset pro_att_set where pro_att_set.isFromB2B = 1 and mageId <> 0', array(), PDO::FETCH_ASSOC);
		if(count($productAttributeSetIds) === 0)
			return;
		
		try
		{
			$transStarted = false;
			try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
			
			foreach($productAttributeSetIds as $productAttributeSetId)
			{
				$productAttributeSetId = $productAttributeSetId['id'];
				$productAttributeSet = ProductAttributeSet::get($productAttributeSetId);
				if(!$productAttributeSet instanceof ProductAttributeSet)
					continue;
				$productAttributes = $this->getProductAttributeList($productAttributeSet->getMageId());
				if(count($productAttributes) === 0)
					continue;
				foreach ($productAttributes as $productAttribute)
				{
					$mageId = trim($productAttribute->attribute_id);
					$code = isset($productAttribute->code) ? trim($productAttribute->code) : '';
					$type = isset($productAttribute->type) ? trim($productAttribute->type) : '';
					if(!isset($productAttribute->required))
						$required = false;
					else $required = (trim($productAttribute->required) === '1' || $required === true || trim($productAttribute->required) === 'true') ? true : false;
					$scope = isset($productAttribute->scope) ? trim($productAttribute->scope) : '';
					$description = isset($productAttribute->description) ? trim($productAttribute->description) : $code;
					
					Log::logging(0, get_class($this), 'getting productAttribute from magento (mageId="' . $mageId . '")', self::LOG_TYPE, '', __FUNCTION__);
					
					$productAttribute = ProductAttribute::getByMageId($mageId);
						if(!$productAttribute instanceof ProductAttribute)
					{
						Log::logging(0, get_class($this), 'found new ProductAttribute from magento(mageId="' . $mageId . '", mageAttributeSetId="' . $productAttributeSet->getMageId() . '", code="' . $code . '", type="' . $type . '", required="' . $required . '", scope="' . $scope . '")', self::LOG_TYPE, '', __FUNCTION__);
						echo 'found new ProductAttribute from magento(mageId="' . $mageId . '", mageAttributeSetId="' . $productAttributeSet->getMageId() . '", code="' . $code . '", type="' . $type . '", required="' . $required . '", scope="' . $scope . '")' . "\n";
						$productAttribute = ProductAttribute::create($code, $type, $required, $scope, $description, true, $mageId, $productAttributeSet->getMageId());
					}
					else
					{
						Log::logging(0, get_class($this), 'found existing ProductAttribute from magento(mageId="' . $mageId . '", mageAttributeSetId="' . $productAttributeSet->getMageId() . '", code="' .$code . '", ID=' . $productAttributeSet->getId() . ', type="' . $type . '", required="' . $required . '", scope="' . $scope . '")', self::LOG_TYPE, '', __FUNCTION__);
						echo 'found existing ProductAttribute from magento(mageId="' . $mageId . '", mageAttributeSetId="' . $productAttributeSet->getMageId() . '", code="' .$code . '", ID=' . $productAttributeSet->getId() . ', type="' . $type . '", required="' . $required . '", scope="' . $scope . '")' . "\n";
						$productAttribute
						->setCode($code)
						->setType($type)
						->setRequired($required)
						->setScope($scope)
						->setDescription($description)
						->setIsFromB2B(true)
						->setAttributeSetMageId($productAttributeSet->getId())
						->setActive(true)
						->save();
					}
				}
			}	
			if($transStarted === false)
				Dao::commitTransaction();
		}
		catch(Exception $e)
		{
			if($transStarted === false)
				Dao::commitTransaction();
			throw $e;
		}
		return $this;
	}
	public function importProductManufacturers()
	{
		$productAttributes = ProductAttribute::getAllByCriteria('code = ? and isFromB2B = 1 and mageId <> 0', array('manufacturer'), true, 1, 1, array("id"=>"desc"));
		if(count($productAttributes) === 0)
			return;
		$productAttribute = $productAttributes[0];
		
		try
		{
			$transStarted = false;
				try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
			foreach($this->getProductAttributeOptions($productAttribute->getMageId()) as $productAttributeOption)
			{
				$label = isset($productAttributeOption->label) ? trim($productAttributeOption->label) : '';
				$value = isset($productAttributeOption->value) ? trim($productAttributeOption->value) : ''; // mageId
				
				if($label === '' || $value === '')
				{
					echo "ingore product manufacturer options due to empty label or value (" . 'label="' . $label . '", value="' . $value . '")' . "\n";
					continue;
				}
				
				$manufacturer = Manufacturer::create($label, '', true, $value);
				echo 'Imported manufacture (name="' . $label . '", mageId=' . $value . ')' . "\n";
				
			}
			if($transStarted === false)
				Dao::commitTransaction();
		}
		catch(Exception $e)
		{
			if($transStarted === false)
				Dao::commitTransaction();
			throw $e;
		}
		return $this;
	}
}
