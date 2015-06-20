<?php
require_once dirname(__FILE__) . '/bootstrap.php';

try {
	echo "Hello<br/>";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	echo '<pre>';
	
	$connector = B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	);
	$session = $connector->login(SystemSettings::TYPE_B2B_SOAP_USER, SystemSettings::TYPE_B2B_SOAP_KEY);
	
	var_dump($session);
	
	
	
	
	echo '</pre>';
	echo "done";
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "<pre>" . $e->getTraceAsString() . "</pre>";
	Dao::rollbackTransaction();
	throw $e;
}