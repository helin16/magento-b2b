<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
				SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY))
	->importProductCategories();
echo 'DONE';