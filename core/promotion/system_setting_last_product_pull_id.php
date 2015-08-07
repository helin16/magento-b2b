<?php
require_once __DIR__ . '/../main/bootstrap.php';
try {
	echo "Begin" . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();

	SystemSettings::addSettings(SystemSettings::TYPE_LAST_PRODUCT_PULL_ID, 0);
	
	Dao::commitTransaction();
} catch (Exception $e)
{
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
