<?php
require_once 'bootstrap.php';
try {
	$product = Product::getBySku('059-08268(WORD13DVD)');
	var_dump($product->getJson(array('mageInfo'=>$product->getMageInfo()))['mageInfo']);
}
catch (Exception $e)
{
	echo "Error:";
	echo $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	throw $e;
}
?>