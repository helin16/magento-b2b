<?php
require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
try {
	echo "Begin" . "\n";
	Dao::beginTransaction();

	$connector = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));
	
	$product = Product::getBySku('HS-210');
	
	if($product instanceof Product) {
		
		echo 'Connecting to Magento for Product ' . $product->getSku() . '(id=' . $product->getId() . ')' . "\n";
		
		$price = 290;
		$result = $connector->updateProductPrice($product->getSku(), $price);
		
		echo ($result === true ? 'Price Successfully updated to $' . $price : '****error' . $result) . "\n";		
	}
	
	
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
?>