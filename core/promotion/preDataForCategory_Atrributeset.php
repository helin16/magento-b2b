<?php
require_once __DIR__ . '/../main/bootstrap.php';
try {
	echo "Begin" . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	
	$data = getDataFromCsv(__DIR__ . '/category_attributeset.csv');
	foreach($data->data as $row)
	{
		$id = trim($row['id']);
		$productAttributesetId = trim($row['productAttributesetId']);
		$obj = ProductCategory::get($id);
		if(!$obj instanceof ProductCategory)
		{
			echo "id " . $id . 'does not exist' . "\n";
			continue;
		}
		$obj->setProductAttributesetId(intval(trim($productAttributesetId)))->save();
	}
	
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}


function getDataFromCsv($file) {
	$file = trim ( $file );
	$paser = new parseCSV ();
	// ftp
	$paser->auto ( $file );
	echo "successfull get data (size=" . sizeof ( $paser->data ) . ") from " . $file . "\n";
	return $paser;
}