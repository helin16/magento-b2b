<?php

ini_set('memory_limit','1024M');

require_once dirname(__FILE__) . '/../../bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
try {
	Dao::beginTransaction();
	
	echo 'Product: id=' . Product::get($argv[1])->getId() . ', sku="' . Product::get($argv[1])->getSku() . '"' . "\n\n";
	
	PriceMatchConnector::run(Product::get($argv[1])->getSku(), true);
	echo '============================================' ."\n";
// 	$rule = ProductPriceMatchRule::create(Product::get($argv[1]), PriceMatchCompany::get(1), '10%', '10%', '-10%');
// 	echo '============================================' ."\n";
	PriceMatchConnector::getMinRecord(Product::get($argv[1])->getSku(), true);
	echo '============================================' ."\n";
	// 136 is qnap
	PriceMatchConnector::getNewPrice(Product::get($argv[1])->getSku(), true, true);
	Dao::commitTransaction();
} catch (Exception $e)
{
	Dao::rollbackTransaction();
	echo "****ERROR****" . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
