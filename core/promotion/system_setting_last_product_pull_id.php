<?php
require_once __DIR__ . '/../main/bootstrap.php';
try {
	echo "Begin" . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();

	$new = count(SystemSettings::getAllByCriteria('type = ?', array(SystemSettings::TYPE_LAST_PRODUCT_PULL_ID), false)) > 0 ? SystemSettings::getAllByCriteria('type = ?', array(SystemSettings::TYPE_LAST_PRODUCT_PULL_ID), true)[0] : new SystemSettings();
	$new->setType(SystemSettings::TYPE_LAST_PRODUCT_PULL_ID)->setActive(true)->setValue(1)->setDescription('last id of pull product from magento to system')->save();

	Dao::commitTransaction();
} catch (Exception $e)
{
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
