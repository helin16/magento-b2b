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
       $this->_runner->log('dataFeedImport: ', __CLASS__ . '::' . __FUNCTION__);
       $sku = $this->_getPram($params, 'sku', null, true);
       $name = $this->_getPram($params, 'name', null, true);
       $shortDesc = $this->_getPram($params, 'shortDesc', $name);
       $fullDesc = $this->_getPram($params, 'fullDesc', '');
       $price = StringUtilsAbstract::getValueFromCurrency($this->_getPram($params, 'price', null, true));
       $supplierName = $this->_getPram($params, 'supplier', null, true);
       $supplier = $this->_getEntityByName($supplierName, 'Supplier');
       if(!$supplier instanceof Supplier)
			throw new Exception("invalid supplier:" . $supplierName);
       
       $brandName = $this->_getPram($params, 'brandName', null, true);
       $manufacturer = Manufacturer::create($brandName);
       $statusName = $this->_getPram($params, 'statusName', null, true);
       $assetAccNo = $this->_getPram($params, 'assetAccNo', null);
       $revenueAccNo = $this->_getPram($params, 'revenueAccNo', null);
       $costAccNo = $this->_getPram($params, 'costAccNo', null);

       //if we have this product already, then skip
       if(!($product = Product::getBySku($sku)) instanceof Product) {
           $this->_runner->log('new SKU(' . $sku . ') for import, creating ...', '', APIService::TAB);
           $product = Product::create($sku, $name, '', null, null, false, $shortDesc, $fullDesc, $manufacturer, $assetAccNo, $revenueAccNo, $costAccNo, null, null, true);
       } else {
           //if there is no price matching rule for this product
           if(ProductPriceMatchRule::countByCriteria('active = 1 and productId = ?', array($product->getId())) === 0) {
               $this->_runner->log('Found SKU(' . $sku . '): ', '', APIService::TAB);
               $this->_runner->log('Updating the price to: ' . StringUtilsAbstract::getCurrency($price), '', APIService::TAB . APIService::TAB);
               //update the price with
               $product->clearAllPrice()
                   ->addPrice(ProductPriceType::get(ProductPriceType::ID_RRP), $price);

               if(!($fullAsset = Asset::getAsset($product->getFullDescAssetId())) instanceof Asset || (file_get_contents($fullAsset->getPath()) === '') ) {

                   $this->_runner->log('GOT BLANK FULL DESD. Updating full description.', '', APIService::TAB . APIService::TAB);
                   if($fullAsset instanceof Asset) {
                       Asset::removeAssets(array($fullAsset->getAssetId()));
                   }
                   $fullAsset = Asset::registerAsset('full_description_for_product.txt', $fullDesc, Asset::TYPE_PRODUCT_DEC);
                   $product->setFullDescAssetId($fullAsset->getAssetId())
                       ->save();
               }
           }
       }
       return $product->getJson();
   }
   private function _getEntityByName($name, $entityName)
   {
		$entities = $entityName::getAllByCriteria('name = ?', array(trim($name)), 1, 1);
		return count($entities) > 0 ? $entities[1] : null;
   }
}