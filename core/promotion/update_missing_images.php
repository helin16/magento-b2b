<?php
/**
 * @author      MagePsycho <info@magepsycho.com>
 * @website     http://www.magepsycho.com
 * @category    Export / Import
 */
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 
set_time_limit(0);
ini_set('memory_limit','1024M');
 
/***************** UTILITY FUNCTIONS ********************/
function _log($message, $file = 'update_missing_images.log'){
    Mage::log($message, null, $file);
}
 
//function _getIndex($field) {
//    global $fields;
//    $result = array_search($field, $fields);
//    if($result === false){
//        $result = -1;
//    }
//    return $result;
//}
 
function _getConnection($type = 'core_read'){
    return Mage::getSingleton('core/resource')->getConnection($type);
}
 
function _getTableName($tableName){
    return Mage::getSingleton('core/resource')->getTableName($tableName);
}
 
function _getAttributeId($attribute_code = 'price'){
    $connection = _getConnection('core_read');
    $sql = "SELECT attribute_id
                FROM " . _getTableName('eav_attribute') . "
            WHERE
                entity_type_id = ?
                AND attribute_code = ?";
    $entity_type_id = _getEntityTypeId();
    return $connection->fetchOne($sql, array($entity_type_id, $attribute_code));
}
 
function _getEntityTypeId($entity_type_code = 'catalog_product'){
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_type_id FROM " . _getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
    return $connection->fetchOne($sql, array($entity_type_code));
}
 
function _getIdFromSku($sku){
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_id FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    return $connection->fetchOne($sql, array($sku));
}
 
function _checkIfSkuExists($sku){
    $connection = _getConnection('core_read');
    $sql        = "SELECT COUNT(*) AS count_no FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    $count      = $connection->fetchOne($sql, array($sku));
    if($count > 0){
        return true;
    }else{
        return false;
    }
}
 
function _checkIfRowExists($productId, $attributeId, $value){
    $tableName  = _getTableName('catalog_product_entity_media_gallery');
    $connection = _getConnection('core_read');
    $sql        = "SELECT COUNT(*) AS count_no FROM " . _getTableName($tableName) . " WHERE entity_id = ? AND attribute_id = ?  AND value = ?";
    $count      = $connection->fetchOne($sql, array($productId, $attributeId, $value));
    if($count > 0){
        return true;
    }else{
        return false;
    }
}
 
function _insertRow($productId, $attributeId, $value){
    $connection             = _getConnection('core_write');
    $tableName              = _getTableName('catalog_product_entity_media_gallery');
 
    $sql = "INSERT INTO " . $tableName . " (attribute_id, entity_id, value) VALUES (?, ?, ?)";
    $connection->query($sql, array($attributeId, $productId, $value));
}
 
function _updateMissingImages($count, $productId){
    $connection             = _getConnection('core_read');
    $smallImageId           = _getAttributeId('small_image');
    $imageId                = _getAttributeId('image');
    $thumbnailId            = _getAttributeId('thumbnail');
    $mediaGalleryId         = _getAttributeId('media_gallery');
 
    //getting small, base, thumbnail images from catalog_product_entity_varchar for a product
    $sql    = "SELECT * FROM " . _getTableName('catalog_product_entity_varchar') . " WHERE attribute_id IN (?, ?, ?) AND entity_id = ? AND `value` != 'no_selection'";
    $rows   = $connection->fetchAll($sql, array($imageId, $smallImageId, $thumbnailId, $productId));
    if(!empty($rows)){
        foreach($rows as $_image){
            //check if that images exist in catalog_product_entity_media_gallery table or not
            if(!_checkIfRowExists($productId, $mediaGalleryId, $_image['value'])){
                //insert that image in catalog_product_entity_media_gallery if it doesn't exist
                _insertRow($productId, $mediaGalleryId, $_image['value']);
                /* Output / Logs */
                $missingImageUpdates = $count . '> Updated:: $productId=' . $productId . ', $image=' . $_image['value'];
                echo $missingImageUpdates.'<br />';
                _log($missingImageUpdates);
            }
        }
        $separator = str_repeat('=', 100);
        _log($separator);
        echo $separator . '<br />';
    }
}
/***************** UTILITY FUNCTIONS ********************/
 
$messages           = array();
//$csv                = new Varien_File_Csv();
//$data               = $csv->getData('update_missing_images.csv'); //path to csv
//$fields             = array_shift($data);
#print_r($fields); print_r($data); exit;

$productsCollection = Mage::getModel('catalog/product')->getCollection();

 

$message = '<hr />';
$count   = 1;
//foreach($data as $_data){
//    $sku                                    = isset($_data[_getIndex('sku')]) ? trim($_data[_getIndex('sku')]) : '';
foreach($productsCollection as $product) {
$sku = $product->getSku();
    if(_checkIfSkuExists($sku)){
        try{
            $productId = _getIdFromSku($sku);
            _updateMissingImages($count, $productId);
            $message .= $count . '> Success:: While Updating Images of Sku (' . $sku . '). <br />';
 
        }catch(Exception $e){
            $message .=  $count .'> Error:: While Upating Images of Sku (' . $sku . ') => '.$e->getMessage().'<br />';
        }
    }else{
        $message .=  $count .'> Error:: Product with Sku (' . $sku . ') does\'t exist.<br />';
    }
    $count++;
}
echo $message;