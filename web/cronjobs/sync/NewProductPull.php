<?php
ini_set('memory_limit','1024M');

require_once dirname(__FILE__) . '/../../bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
echo "Begin MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";

importNewProduct();

try
{
	Dao::beginTransaction();
	
	if(($systemSetting = SystemSettings::getByType(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL)) instanceof SystemSettings)
		$systemSetting->setValue(UDate::now()->__toString())->save();
	else throw new Exception('cannot set LAST_NEW_PRODUCT_PULL in system setting');
	
	Dao::commitTransaction();
} catch (Exception $e)
{
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}

function importNewProduct()
{
	$script = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	)
	->importProducts(true, true);
}