<?php

require_once 'bootstrap.php';

echo '<pre>';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
$wsdl = 'http://budgetpc.com.au/api/v2_soap?wsdl=1';
$connector = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
// 		SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
		$wsdl, 
		SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
		SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));
$connector->importProducts();
var_dump($productInfo);
echo 'DONE';