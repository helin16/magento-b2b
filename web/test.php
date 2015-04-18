<?php
require_once 'bootstrap.php';
try {
	Dao::beginTransaction();
	echo '<pre>';
	$result = B2BConnector::getConnector(B2BConnector::CONNECTOR_TYPE_ORDER,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY)
	)->getOrderInfo('BPC00030375');
	var_dump($result['items'][0]['product_options']);
	echo '</pre>';

	Dao::commitTransaction();
} catch (Exception $e)
{ 
	Dao::rollbackTransaction();
	throw $e;
}
?>