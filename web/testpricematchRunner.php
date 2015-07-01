<?php

require_once 'bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

echo "clear all PriceMatchMin" . "\n";
PriceMatchMin::deleteByCriteria('id <> 0'); // this will delete all b/c id will never be 0
echo "clear all PriceMatchRecord" . "\n" . "\n";
PriceMatchRecord::deleteByCriteria('id <> 0'); // this will delete all b/c id will never be 0

$productIds = Dao::getResultsNative('select distinct id from product where active = 1 and id > 42556', array(), PDO::FETCH_ASSOC);

foreach ($productIds as $row)
{
	$output = '';
	exec('php ' . dirname(__FILE__). '\testpricematch.php ' . $row['id'], $output);
	echo print_r($output, true) . "\n";
}