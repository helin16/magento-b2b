<?php
require_once dirname(__FILE__) . '/../main/bootstrap.php';
try {
	echo "Begin" . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	
	foreach (Product::getAllByCriteria('stockOnOrder < 0') as $product)
	{
		echo $product->getSku() . "\n";
		$product->setStockOnOrder(0)->snapshotQty(null,ProductQtyLog::TYPE_STOCK_ADJ,'stock adjustment for negative stock on order')->save();
	}

	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo $e->getTraceAsString();
	Dao::rollbackTransaction();
	throw $e;
}
?>