<?php
ini_set('memory_limit','1024M');

require_once dirname(__FILE__) . '/bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
echo "Begin test from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";

$sku = '8677';
$info = getProductInfo($sku);
var_dump($info);

echo "Done test from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";

function getProductInfo($sku)
{
	$script = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	);
	return $script->getProductInfo($sku);
}
function updateProductInfo($sku)
{
	$array = array('sku'=>'C5-0.5-Orange-1');
	$script = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	);
	return $script->updateProductInfo($sku,$array);
}