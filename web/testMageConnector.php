<?php
require_once 'bootstrap.php';
try {
	var_dump(MageOrderConnector::getLastestOrders('2015-08-01'));
// 	$options = array('exceptions' => true, 'trace'=> true, 'encoding'=>'utf-8');
// 	$options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal", 'proxy_port' => 3128));

// 	$wsdl = 'http://backup.budgetpc.com.au/api/v2_soap?wsdl=1';
// 	$apiUser = 'B2BUser';
// 	$apiKey = 'B2BUser';
// 	$_soapClient = new SoapClient($wsdl, $options);
// 	$_sessionId = $_soapClient->login($apiUser, $apiKey);
// 	var_dump($_sessionId);
}
catch (Exception $e)
{
	echo "<pre>";
	echo "Error:";
	echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	echo "</pre>";
}
?>