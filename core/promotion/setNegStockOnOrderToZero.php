<?php
require_once dirname(__FILE__) . '/../main/bootstrap.php';
try {
	echo "Hello<br/>";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	
	foreach (Product::getAllByCriteria('stockOnOrder < 0') as $product)
	{
		$product->setStockOnOrder(0)->snapshotQty(null,ProductQtyLog::TYPE_STOCK_ADJ,'stock adjustment for negative stock on order')->save();
	}

	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "<pre>" . $e->getTraceAsString() . "</pre>";
	Dao::rollbackTransaction();
	throw $e;
}
?>