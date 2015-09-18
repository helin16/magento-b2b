<?php
ini_set('memory_limit','1024M');

require_once dirname(__FILE__) . '/../../bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
echo "Begin importProducts from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";

importNewProduct();

echo "Done importProducts from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";


function importNewProduct()
{
	$debug = true;
	$newOnly = false;
	$script = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	)
	->processDownloadedProductInfo($newOnly, $debug);
}