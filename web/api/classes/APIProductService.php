<?php
class APIProductService extends APIServiceAbstract
{
   protected $entityName = 'Product';
   /**
    * Data feed import POST
    *
    * @param array $params
    *
    * @return array
    */
   public function post_dataFeedImport($params)
   {
       return $this->_dataFeedImport($params);
   }
   /**
    * Data feed import PUT
    *
    * @param array $params
    *
    * @return array
    */
   public function put_dataFeedImport($params)
   {
       return $this->_dataFeedImport($params);
   }
   /**
    * create/update product via datafeed.
    *
    * @param array $params
    *
    * @return array
    */
   private function _dataFeedImport($params)
   {
   	   try{
   	   	   Dao::beginTransaction();
	       $this->_runner->log('dataFeedImport: ', __CLASS__ . '::' . __FUNCTION__);
	       $sku = $this->_getPram($params, 'sku', null, true);
	       $name = $this->_getPram($params, 'name', null, true);
	       $shortDesc = $this->_getPram($params, 'short_description', $name);
	       $fullDesc = $this->_getPram($params, 'description', '');
	       $price = StringUtilsAbstract::getValueFromCurrency($this->_getPram($params, 'price', null, true));
	       $supplierName = $this->_getPram($params, 'supplier', null, true);
	       $supplierCode = $this->_getPram($params, 'supplier_code', null, true);
	       $supplier = $this->_getEntityByName($supplierName, 'Supplier');
	       if(!$supplier instanceof Supplier)
				throw new Exception("invalid supplier:" . $supplierName);

	       $manufacturerId = $this->_getPram($params, 'manufacturer_id', null, true);
	       $manufacturer = Manufacturer::get($manufacturerId);
	       if(!$manufacturer instanceof Manufacturer)
	       		throw new Exception("invalid Manufacturer:" . $manufacturerId);
	       $statusName = $this->_getPram($params, 'availability', null, true);
	       $status = $this->_getEntityByName($statusName, 'ProductStatus');
	       if(!$status instanceof ProductStatus)
	       	throw new Exception("invalid ProductStatus:" . $statusName);

	       $assetAccNo = $this->_getPram($params, 'assetAccNo', null);
	       $revenueAccNo = $this->_getPram($params, 'revenueAccNo', null);
	       $costAccNo = $this->_getPram($params, 'costAccNo', null);
	       $categoryIds = $this->_getPram($params, 'category_ids', array());
	       $canSupplyQty = $this->_getPram($params, 'qty', 0);
	       $weight = $this->_getPram($params, 'weight', 0);
	       $images = $this->_getPram($params, 'images', array());

	       $canUpdate = false;

	       //if we have this product already, then skip
	       if (!($product = Product::getBySku($sku)) instanceof Product) {
	           $this->_runner->log('new SKU(' . $sku . ') for import, creating ...', '', APIService::TAB);
	           $product = Product::create($sku, $name, '', null, null, false, $shortDesc, $fullDesc, $manufacturer, $assetAccNo, $revenueAccNo, $costAccNo, null, null, true);
		       $canUpdate = true;
	       } else {
	           //if there is no price matching rule for this product
	           if (($rulesCount = intval(ProductPriceMatchRule::countByCriteria('active = 1 and productId = ?', array($product->getId())))) === 0) {
	               $this->_runner->log('Found SKU(' . $sku . '): ', '', APIService::TAB);
	               $this->_runner->log('Updating the price to: ' . StringUtilsAbstract::getCurrency($price), '', APIService::TAB . APIService::TAB);
	               //update the price with
	               $product->clearAllPrice()
	                   ->addPrice(ProductPriceType::get(ProductPriceType::ID_RRP), $price);

	               $fullAsset = Asset::getAsset($product->getFullDescAssetId());
	               $this->_runner->log('Finding asset for full description, assetId:' . ($fullAsset instanceof Asset ? $fullAsset->getAssetId() : ''), '', APIService::TAB . APIService::TAB);
	               $fullAssetContent = '';
	               if ($fullAsset instanceof Asset) {
	               	   $fullAssetContent = file_get_contents($fullAsset->getPath());
		               $this->_runner->log('Got full asset content before html_decode: <' . $fullAssetContent . '>', '', APIService::TAB . APIService::TAB);
		               $fullAssetContent= trim(str_replace('&nbsp;', '', $fullAssetContent));
		               $this->_runner->log('Got full asset content after html_code: <' . $fullAssetContent . '>', '', APIService::TAB . APIService::TAB);
	               }
	               if ($fullAssetContent === '') {

	                   $this->_runner->log('GOT BLANK FULL DESD. Updating full description.', '', APIService::TAB . APIService::TAB . APIService::TAB);
	                   if ($fullAsset instanceof Asset) {
	                       Asset::removeAssets(array($fullAsset->getAssetId()));
			       		   $this->_runner->log('REMOVED old empty asset for full description', '', APIService::TAB . APIService::TAB . APIService::TAB);
	                   }
	                   $fullAsset = Asset::registerAsset('full_description_for_product.txt', $fullDesc, Asset::TYPE_PRODUCT_DEC);
	                   $product->setFullDescAssetId($fullAsset->getAssetId())
	                       ->save();
		       		   $this->_runner->log('Added a new full description with assetId: ' . $fullAsset->getAssetId(), '', APIService::TAB . APIService::TAB);
	               }
			       $canUpdate = true;
	           } else {
	           	  $this->_runner->log('SKIP updating. Found ProductPriceMatchRule count:' . $rulesCount, '', APIService::TAB);
	           }
	       }
	       $json = $product->getJson();

	       //only update categories and status when there is no pricematching rule or created new
	       if ($canUpdate === true) {
		       if (is_array($categoryIds) && count($categoryIds) > 0) {
		       		$this->_runner->log('Updating the categories: ' . implode(', ', $categoryIds), '', APIService::TAB . APIService::TAB);
		       		foreach ($categoryIds as $categoryId) {
		       			if (!($category = ProductCategory::get($categoryId)) instanceof ProductCategory)
		       				continue;
		       			if (count($ids = explode(ProductCategory::POSITION_SEPARATOR, trim($category->getPosition()))) > 0) {
		       				foreach (ProductCategory::getAllByCriteria('id in (' . implode(',', $ids) . ')') as $cate) {
		       					$product->addCategory($cate);
					       		$this->_runner->log('Updated Category ID: ' . $cate->getId(), '', APIService::TAB . APIService::TAB . APIService::TAB);
		       				}
		       			}
		       		}
		       }
		       //updating the images
		       if (is_array($images) && count($images) > 0) {
		           $this->_runner->log('Processing ' . count($images) . ' image(s) ...', '', APIService::TAB . APIService::TAB);
		           $exisitingImgsKeys = array();
		           $this->_runner->log('Checking exsiting images...', '', APIService::TAB . APIService::TAB . APIService::TAB);
		           $exisitingImgs = $product->getImages();
		           $this->_runner->log('Got ' . count($exisitingImgs) . ' exisiting image(s), keys: ', '', APIService::TAB . APIService::TAB . APIService::TAB . APIService::TAB);
	               foreach ($exisitingImgs as $image) {
	                   if ((($asset = Asset::getAsset($image->getImageAssetId())) instanceof Asset)) {
	                       $imgKey = md5($asset->read());
    	                   $exisitingImgsKeys[] = $imgKey;
        	               $this->_runner->log($imgKey, '', APIService::TAB . APIService::TAB . APIService::TAB . APIService::TAB . APIService::TAB);
	                   }
	               }
	               $this->_runner->log('Checking ' . count($images) . ' new image(s) ...', '', APIService::TAB . APIService::TAB);
		           foreach ($images as $image) {
		               //if haven't got any content at all
		               if (!isset($image['content'])) {
        	               $this->_runner->log('No Content, SKIP!', '', APIService::TAB . APIService::TAB . APIService::TAB);
		                   continue;
		               }
		               $newImageContent = base64_decode($image['content']);
		               $newImgKey = md5($newImageContent);
		               //if we've got the image already
		               if (in_array($newImgKey, $exisitingImgsKeys)) {
        	               $this->_runner->log('Same Image Exists[' . $newImgKey . '], SKIP!', '', APIService::TAB . APIService::TAB . APIService::TAB);
		                   continue;
		               }
		               $asset = Asset::registerAsset($image['name'], $newImageContent, Asset::TYPE_PRODUCT_IMG);
		               $this->_runner->log('Registered a new Asset [AssetID=' . $asset->getAssetId() . '].', '', APIService::TAB . APIService::TAB . APIService::TAB);
		               $product->addImage($asset);
		               $this->_runner->log('Added to product(SKU=' . $product->getSku() . ')', '', APIService::TAB . APIService::TAB . APIService::TAB);

		           }
		       }
		       $product->setStatus($status);
		       $this->_runner->log('Updated Status to: ' . $status->getName(), '', APIService::TAB . APIService::TAB);
		       $product->addSupplier($supplier, $supplierCode, $canSupplyQty);
		       $this->_runner->log('Updated Supplier(ID' . $supplier->getId() . ', name=' . $supplier->getName() . ') with code: ' . $supplierCode . 'canSupplyQty=' . $canSupplyQty, '', APIService::TAB . APIService::TAB);
		       $json = $product->save()->getJson();
		       $this->_runner->log('Saved Product ID: ' . $product->getId(), '', APIService::TAB . APIService::TAB);
	       }
	       Dao::commitTransaction();
	       return $json;
   	   } catch (Exception $e) {
   	   		Dao::rollbackTransaction();
   	   		throw $e;
   	   }
   }
   private function _getEntityByName($name, $entityName)
   {
		$entities = $entityName::getAllByCriteria('name = ?', array(trim($name)), 1, 1);
		return count($entities) > 0 ? $entities[0] : null;
   }
}