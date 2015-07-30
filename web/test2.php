<?php
require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
try {
	Dao::beginTransaction();
	echo '<pre>';
	
	$orderItems = OrderItem::getAllByCriteria('productId = 3129');
	foreach ($orderItems as $orderItem) {
		$order = $orderItem->getOrder();
		echo implode("\t", $order->getJson()) . "\n";
	}
	
	echo '</pre>';

	Dao::commitTransaction();
} catch (Exception $e)
{ 
	Dao::rollbackTransaction();
	throw $e;
}
?>