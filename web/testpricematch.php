<?php

ini_set('memory_limit','64M');

require_once 'bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
try {
	Dao::beginTransaction();
	
	
	echo 'Product: id=' . Product::get($argv[1])->getId() . ', sku="' . Product::get($argv[1])->getSku() . '"' . "\n\n";
	
	PriceMatchConnector::run(Product::get($argv[1])->getSku(), true);
	Dao::commitTransaction();
} catch (Exception $e)
{
	Dao::rollbackTransaction();
	echo "****ERROR****" . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
