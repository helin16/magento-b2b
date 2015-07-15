<?php
require_once 'bootstrap.php';
try {
	echo "Begin" . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	
	if(($systemSetting = SystemSettings::getSettings(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL)) instanceof SystemSettings)
		$systemSetting->setValue(UDate::now())->save();
	
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
?>