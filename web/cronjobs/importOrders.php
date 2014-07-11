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
}
function shipOrder($orderNo)
{
	$shippment = FactoryAbastract::service('Shippment')->get(1);
	$order = FactoryAbastract::service('Order')->findByCriteria('orderNo = ?', array($orderNo), true, 1,1);
	$script = B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_SHIP,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	)
	->shipOrder($order[0], $shippment, array(), 'testing shipping', true, true);
}


Core::setUser(FactoryAbastract::service('UserAccount')->get(UserAccount::ID_SYSTEM_ACCOUNT));
importOrder();


echo "done";