<?php
require_once dirname(__FILE__) . '/../main/bootstrap.php';
try {
	echo "Begin" . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	
	$new = count(SystemSettings::getAllByCriteria('type = ?', array(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL), false)) > 0 ? SystemSettings::getAllByCriteria('type = ?', array(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL), true)[0] : new SystemSettings();
	$new->setType(SystemSettings::TYPE_LAST_NEW_PRODUCT_PULL)->setActive(true)->setValue(UDate::zeroDate())->setDescription('last timestamp pull NEW product from magento to system')->save();
	$new = count(SystemSettings::getAllByCriteria('type = ?', array(SystemSettings::TYPE_LAST_NEW_PRODUCT_PUSH), false)) > 0 ? SystemSettings::getAllByCriteria('type = ?', array(SystemSettings::TYPE_LAST_NEW_PRODUCT_PUSH), true)[0] : new SystemSettings();
	$new->setType(SystemSettings::TYPE_LAST_NEW_PRODUCT_PUSH)->setActive(true)->setValue(UDate::zeroDate())->setDescription('last timestamp push NEW product price from system to magento')->save();
	
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
?>