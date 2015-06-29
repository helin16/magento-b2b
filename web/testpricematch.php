<?php
require_once 'bootstrap.php';
try {
	echo "Hello<br/>";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	echo '<pre>';

	$sku = Product::get(2244)->getSku();
	$min = PriceMatchMin::create($sku);
// 	var_dump($min);
	$record = PriceMatchRecord::create(PriceMatchCompany::get(1), $min, '12.2');
	$record = PriceMatchRecord::create(PriceMatchCompany::get(2), $min, '2.2');
	$record = PriceMatchRecord::create(PriceMatchCompany::get(3), $min, '6.2');
	
	$min->getMin(array('componieIds'=>array(1,3)));
	
	var_dump($record);
	
	
	echo '</pre>';
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	Dao::rollbackTransaction();
	throw $e;
}
?>