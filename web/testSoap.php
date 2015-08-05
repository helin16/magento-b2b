<?php
require_once 'bootstrap.php';
try {
	echo "Begin" . __CLASS__ . " Melb Time: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	
	$wsdl = "http://localhost:8888/?soap=product.wsdl";
	$sku = "test2007153";
	$name = "namefor" . $sku;
	
	$soap = ComScriptSoap::getScript($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE));
// 	// create product
// 	$productXML = $soap->createProduct($sku, $name);
// 	// create product
	$productXML = $soap->getProductBySku($sku);
	
	var_dump($productXML);
	die;
	if(intval($productXML['status']) === 1)
		throw new Exception(trim($productXML->error));
	
	
	$product = $productXML->product;
	$productArray = json_decode($product,true);
	
	var_dump($productArray);
	
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
?>