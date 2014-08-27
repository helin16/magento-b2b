<?php
class ProductUpdateConnector extends B2BConnector
{
	private $_mySoapClient;
	
	public function syncProductAndProductCategory()
	{
		$cronStartDateTime = UDate::now();
		
		$criteria = array("(sku != '' OR (mageId != '' AND mageId != 0))");
		$param = array();
		
		if(($lastUpdateDateTime = trim(SystemSettings::getSettings(SystemSettings::TYPE_PRODUCT_LAST_UPDATED))) !== '')
		{
			$criteria[] = "updated >= ?";
			$param[] = $lastUpdateDateTime;
		}
			
		$counter = Product::countByCriteria(implode(" and ", $criteria), $param);
		if($counter > 0)
		{
			$this->_mySoapClient = $this->_connect();
			
			$this->syncAllProductCategory();
			$productArray = Product::findByCriteria(implode(" and ", $criteria), $param);
			foreach($productArray as $product)
			{
				$linkedCategories = array();
				$product_categoryArray = Product_Category::getCategories($product);
				if(is_array($product_categoryArray) && count($product_categoryArray) > 0)
					$linkedCategories = array_map(create_function('$a', 'return $a->getCategory()->getMageId();'), $product_categoryArray);
				
				/// if no category is found, set the default to 1 /// 
				if(count($linkedCategories) <= 0)
					$linkedCategories = array(1);
				
				$productData = $this->_generateProductData($product, $linkedCategories);
				if(($productMageId = trim($product->getMageId())) === '' || $productMageId === '0')
				{	
					$attributeSets = $this->_mySoapClient->catalogProductAttributeSetList($session);
					$attributeSet = current($attributeSets);
					$productType = 'simple';
					
					$newMageId = $this->_mySoapClient->catalogProductCreate($this->_session, $productType, $attributeSet->set_id, trim($product->getSku()), $productData);
					if(is_numeric($newMageId))
						$product->setMageId($newMageId)->save();
					else 
						$this->_handle_failed_product($product, $cronStartDateTime);	
				}
				else
				{
					// update product on magento
					$updated = $this->_mySoapClient->catalogProductUpdate($this->_session, $productMageId, $productData);
					if($updated === false)
						$this->_handle_failed_product($product, $cronStartDateTime, "update");
				}
			}
		}
		
		SystemSettings::addSettings(SystemSettings::TYPE_PRODUCT_LAST_UPDATED, $cronStartDateTime);
		//Debug::inspect($products); die();
	}
	
	/**
	 * 
	 * @param Product $product
	 * @param unknown $cronStartDateTime
	 * @param string $syncType
	 */
	private function _handle_failed_product(Product $product, $cronStartDateTime, $syncType = "insert")
	{
		$date = new UDate(trim($cronStartDateTime));
		$date->modify('+1 second');
		$product->setUpdated($date)->save();
		Log::LogEntity($product, 'Product Sync failed with Magento. '.$syncType.' operation failed', Log::TYPE_SYSTEM);
	}
	
	/**
	 * This function generates the ProductData for a product to be used for inser/update of product on Magento
	 * @param Product $product
	 * @param unknown $linkedCategories
	 * @return multitype:string number unknown multitype:number
	 */
	private function _generateProductData(Product $product, Array $linkedCategories)
	{
		$productDescription = '';
		$productDescAssetId = trim($product->getFullDescAssetId());
		if($productDescAssetId !== '')
		{
			$asset = Asset::getAsset($productDescAssetId);
			if($asset instanceof Asset && ($assetPath = trim($asset->getPath())) !== '')
				$productDescription = Asset::readAssetFile($assetPath);
		}
		
		$price = '0';
		$productPrices = ProductPrice::getPrices($product, ProductPriceType::get(ProductPriceType::ID_RRP));
		if(count($productPrices) > 0)
			$price = $productPrices[0]->getPrice();
		
		$urlKey = strtolower(str_replace(' ', '-', trim($product->getName())));
		
		return array('categories' => $linkedCategories,
				     'websites' => array(1),
				     'name' => trim($product->getName()),
				     'description' => $productDescription,
				     'short_description' => trim($product->getShortDescription()),
				     'weight' => (method_exists($product, 'getWeight') ? trim($product->getWeight()) : '1'),
				     'status' => trim($product->getStatus()),
				     'url_key' => $urlKey,
				     'url_path' => $urlKey,
				     'visibility' => '4',
				     'price' => $price,
				     'tax_class_id' => 1,
				     'meta_title' => trim($product->getName()),
				     'meta_keyword' => trim($product->getSku()),
				     'meta_description' => trim($product->getShortDescription()));
	}
	
	/**
	 * This function generates the CategoryData for the Product Category insert / update to Magento 
	 * 
	 * @param ProductCategory $productCategory
	 * 
	 * @return Array
	 */
	private function _generateCategoryData(ProductCategory $productCategory)
	{
		return array('name' => trim($productCategory->getName()),
					 'is_active' => (int)$productCategory->getActive(),
					 'available_sort_by' => array('created_at'),
					 'custom_design' => null,
					 'custom_apply_to_products' => null,
					 'custom_design_from' => null,
					 'custom_design_to' => null,
					 'custom_layout_update' => null,
					 'default_sort_by' => 'position',
					 'description' => trim($productCategory->getDescription()),
					 'display_mode' => null,
					 'is_anchor' => (int)$productCategory->getIsAnchor(),
					 'landing_page' => null,
					 'meta_description' => trim($productCategory->getDescription()),
					 'meta_keywords' => trim($productCategory->getDescription()),
					 'meta_title' => trim($productCategory->getName()),
					 'page_layout' => 'No layout updates',
					 'url_key' => trim($productCategory->getUrlKey()),
					 'include_in_menu' => (int)$productCategory->getIncludeInMenu()
				);
	}
	
	public function syncAllProductCategory()
	{
		$pcArray = ProductCategory::getAll(true, null, DaoQuery::DEFAUTL_PAGE_SIZE, array('position' => 'ASC'));
		if(is_array($pcArray) && count($pcArray) > 0)
		{
			$soapClient = $this->_mySoapClient;
			foreach($pcArray as $productCategory)
			{
				$categoryData = $this->_generateCategoryData($productCategory);
				if(($pcMageId = trim($productCategory->mageId)) === '' || $pcMageId === '0')
				{
					$parentId = 1;
					if(($pcParent = $productCategory->getParent()) instanceof ProductCategory)
						$parentId = (int)$pcParent->getMageId();
					
					$newMageId = $soapClient->catalogCategoryCreate($this->_session, $parentId, $categoryData);
					$productCategory->setMageId($newMageId)->save();
				}
				else
					$updated = $soapClient->catalogCategoryUpdate($this->_session, $pcMageId, $categoryData);
			}
		}
		return $this;
	}
	
}