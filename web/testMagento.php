<?php
require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
try {
	echo "Begin" . "\n";
	Dao::beginTransaction();

	$wsdl = 'http://www.budgetpc.com.au/api/v2_soap?wsdl=1';
	$options = array('exceptions' => true, 'encoding'=>'utf-8', 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP);
	$options = array_merge($options, array('proxy_host' => "proxy.bytecraft.internal", 'proxy_port' => 3128));
	$soapClient = ComScriptSoap::getScript($wsdl, $options);

	$session = $soapClient->login('B2BUser', 'B2BUser');
	$params = array(
			'complex_filter' => array(
					array(
							'key' => 'created_at',
							'value' => array(
									'key' => 'gt',
									'value' => '2015-08-03 00:10:02'
							),
					),
			)
	);
	$result = $soapClient->salesOrderList($session, $params);
	var_dump(is_soap_fault($result));
// 	var_dump($result->faultcode);
// 	var_dump($result->faultstring);
// 	$result = $connector->getProductInfo('test-graphic-card');

	var_dump($result);

	Dao::commitTransaction();
} catch (Exception $e)
{
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
?>