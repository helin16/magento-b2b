<?php

require_once 'bootstrap.php';

Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

$productIds = Dao::getResultsNative('select distinct id from product where active = 1 and id > 42556', array(), PDO::FETCH_ASSOC);

foreach ($productIds as $row)
{
	$output = '';
	exec('php ' . dirname(__FILE__). '\testpricematch.php ' . $row['id'], $output);
	echo print_r($output, true) . "\n";
}