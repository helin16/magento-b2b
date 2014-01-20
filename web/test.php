<?php
require 'bootstrap.php';
Core::setUser(FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
$client = new B2BConnector(SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL), 
		SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER), 
		SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));
$result = $client->importOrders();
var_dump($result);