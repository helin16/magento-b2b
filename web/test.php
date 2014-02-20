<?php
require 'bootstrap.php';
Core::setUser(FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
// $result = B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER, 
// 	'http://ccbooks.com.au/index.php/api/v2_soap/?wsdl', 
// 	SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER), 
// 	SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY))->getOrderInfo('100000020');
$result = B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_SHIP, 
	'http://ccbooks.com.au/index.php/api/v2_soap/?wsdl', 
	SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER), 
	SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY))->getCouriers('100000020');
var_dump($result);