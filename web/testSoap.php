<?php
require_once 'bootstrap.php';
try {
	echo "Begin" . __CLASS__ . " Melb Time: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n <pre>";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
// 	Dao::beginTransaction();

	$wsdl = "http://localhost:8081/?soap=product.wsdl";
	$sku = "FVS336G-V2";
	$name = "namefor" . $sku;
	$id = 188;

	$options = array('exceptions' => true, 'trace'=> true, 'encoding'=>'utf-8', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP);
	$soapClient = new SoapClient($wsdl, $options);
// 	// create product
// 	$productXML = $soap->createProduct($sku, $name);
// 	// create product
	$productXML = $soapClient->getProductBySku($sku);
	echo "Response:<textarea>" .  $soapClient->__getLastResponse() . "</textarea>";

	$productXML = new SimpleXMLElement($productXML);
	var_dump($productXML);

	if(intval($productXML['status']) === 1)
		throw new Exception(trim($productXML->error));

	$obj = $productXML->category;
	$productArray = json_decode($obj,true);

// 	var_dump($productArray);

// 	Dao::commitTransaction();
}
catch (Exception $e)
{
	echo "Error:";
	echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
// 	Dao::rollbackTransaction();
	throw $e;
}
?>