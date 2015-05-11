<?php
require_once 'bootstrap.php';
try {
	echo "Hello<br/>";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	echo '<pre>';

	$product = Product::get(38762);
	$lastPurchaseTime = UDate::now();
	$receivingItem = ReceivingItem::get(25);
	$comments = 'cccccccccccc';
	
	$newAgeingLog = ProductAgeingLog::create($product, $lastPurchaseTime, $comments);
	var_dump($newAgeingLog->getJson());
	echo '</pre>';

	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "<pre>" . $e->getTraceAsString() . "</pre>";
	Dao::rollbackTransaction();
	throw $e;
}
?>