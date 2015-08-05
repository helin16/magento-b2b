<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
function importOrder()
{
	$script = B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	)
	->importOrders();
	echo implode($script->getLogs());
}
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
importOrder();
echo "\n\n";