<?php
require_once dirname(__FILE__) . '/bootstrap.php';

try {
	echo "Hello<br/>";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	echo '<pre>';
	
	$data = array(1,2,3);
	var_dump(SupplierConnector::processDatafeed(Supplier::get(15), $data));
	
	echo '</pre>';
	echo "done";
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "<pre>" . $e->getTraceAsString() . "</pre>";
	Dao::rollbackTransaction();
	throw $e;
}