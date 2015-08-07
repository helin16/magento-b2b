<?php
require_once 'bootstrap.php';
try {
	$soapClient = null;
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
// 	// get product
// 	$resultXml = $soapClient->getProductBySku($sku);
	$resultXml = $soapClient->getCategory($id);

	echo "Response:<textarea>" .  $soapClient->__getLastResponse() . "</textarea>";

	$resultXml = new SimpleXMLElement($resultXml);
// 	var_dump($productXML->product);
	var_dump('original category json String: ' . $resultXml->category);
	var_dump('decoded json String: ' . print_r(json_decode($resultXml->category, true), true));

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
	if($soapClient instanceof SoapClient)
		echo "Response:<textarea>" .  $soapClient->__getLastResponse() . "</textarea>";
	echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
// 	Dao::rollbackTransaction();
	throw $e;
}
?>