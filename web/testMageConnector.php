<?php
require_once 'bootstrap.php';
try {
	$sku = "VS247HV";
	$param = array(
		'newInfo' => array(
			'price' => 189.00
		)
		,'newAttributes' => array(
			'all_ln_stock' => 'Ships In 24Hrs'
		)
	);
	$connector = CatelogConnector::getConnector(B2BConnector::CONNECTOR_TYPE_CATELOG,
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_WSDL),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_USER),
			SystemSettings::getSettings(SystemSettings::TYPE_B2B_SOAP_KEY));
	$connector->updateProductInfo($sku, $param);
}
catch (Exception $e)
{
	echo "Error:";
	echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	throw $e;
}
?>