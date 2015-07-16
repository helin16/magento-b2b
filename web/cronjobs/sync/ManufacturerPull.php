<?php
/**
 * run this one AFTER run attribute set pull
 */
require_once dirname(__FILE__) . '/../../bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
echo "Begin importAttributes from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";
CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY))
	->importProductAttributes();
echo "Done importAttributes from magento MELB TIME: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n";