<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
$supplier = "synnex";
$filePath = "/tmp/datafeed/" . $supplier . "_sku_correction.csv";

$rowCount = 0;
$successCount = 0;

try {
	echo 'START: correct sku for supplier ' . $supplier . PHP_EOL;
	$transStarted = false;
	try {Dao::beginTransaction();} catch(Exception $e) {$transStarted = true;}
	foreach (($data = readData($filePath)) as $row)
	{
		echo 'rowCount: ' . $rowCount . PHP_EOL;
		$sup_code = trim($row['sup_code']);
		$man_code = trim($row['man_code']);
		
		if($sup_code !== $man_code && ($obj = Product::getBySku($sup_code)) instanceof Product)
		{
			if(($obj2 = Product::getBySku($man_code)) instanceof Product)
			{
				echo '***warning***' . 'try to correct Product[' . $obj->getId() . '] sku from ' . $sup_code . ' to ' . $man_code . ', but target sku already exist. ';
				if(intval($obj2->getStockOnHand()) === 0 && intval($obj2->getStockOnOrder()) === 0 && intval($obj2->getStockOnPO()) === 0 )
				{
					Product::deleteByCriteria('id = ?', array($obj2->getId()));
					echo ' since stock on hand/order/po is all zero, delete product[' . $obj2->getId() . '] ' . $obj2->getSku() . PHP_EOL;
				} else {
					echo ' skipped ' . PHP_EOL;
					echo print_r($obj2->getJson(), true);
					continue;
				}
			}
			$obj->setSku($man_code)->save();
			echo 'success: ' . 'correct Product[' . $obj->getId() . '] sku from ' . $sup_code . ' to ' . $man_code . PHP_EOL;
			$successCount++;
		}
		$rowCount++;
	}
	if($transStarted === false)
	{
		Dao::commitTransaction();
		echo '***RESULT***' . ' ' . $successCount . ' out of ' . $rowCount . ' are corrected' . PHP_EOL;
	} else { echo '***warning***' . '$transStarted === true' . PHP_EOL; }
} catch (SoapFault $e) {
	if($transStarted === false)
		Dao::rollbackTransaction();
	var_dump($e);
	throw $e;
}

function readData($filePath)
{
	$csv = new parseCSV();
	$csv->auto($filePath);
	return $csv->data;
}

function getProductArray($product, $pro) 
{
	if(is_null($pro) || !isset($pro->additional_attributes))
		return array();
	$proArray = array();
	$proArray['sku'] = trim($product->sku);
	$proArray['name'] = trim($product->name);
	$proArray['product_id'] = trim($product->product_id);
	$proArray['attributeSetId'] = trim($pro->set);
	foreach($pro->additional_attributes as $row)
		$proArray[$row->key] = trim($row->value);
	return $proArray;
}

function removeLineFromFile($fileName, $line)
{
	$contents = file_get_contents($fileName);
	$contents = str_replace($line, '', $contents);
	file_put_contents($fileName, $contents);
}
