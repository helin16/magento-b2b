<?php
ini_set('memory_limit','1024M');

require_once dirname(__FILE__) . '/../../bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
try {
	echo "Begin MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";
	Dao::beginTransaction();

	$connector = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));

	$result = $connector->importProducts(false, true);
	// 	$result = $connector->getProductInfo('test-graphic-card');

	// 	var_dump($result);

	Dao::commitTransaction();
} catch (Exception $e)
{
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}