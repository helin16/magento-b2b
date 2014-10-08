<?php

require_once 'bootstrap.php';

$wsdl = 'http://budgetpc.com.au/api/v2_soap?wsdl=1';
$product = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG, $wsdl, SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER), SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY))
	->getProductInfo('FVS336G-V2');
var_dump($product);
