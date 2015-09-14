<?php
require_once dirname(__FILE__) . '/../main/bootstrap.php';
try {
	$soapClient = null;
	echo "Begin" . __CLASS__ . " Melb Time: " . UDate::now(UDate::TIME_ZONE_MELB) . "\n <pre>";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();

	SystemSettings::addSettings('system_build_product_ids', json_encode(array()), 'the product id json for system builds');

	Dao::commitTransaction();
}
catch (Exception $e)
{
	echo "Error:";
	if($soapClient instanceof SoapClient)
		echo "Response:<textarea>" .  $soapClient->__getLastResponse() . "</textarea>";
	echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
