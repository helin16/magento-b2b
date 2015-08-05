<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/mmtConnector.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

try {
	
	$debug = true;
	$now = str_replace(' ', '_', UDate::now(UDate::TIME_ZONE_MELB)->getDateTimeString());
	$feed_from_magento = dirname(__FILE__) . "/export_product.csv";
	$feed_from_web = dirname(__FILE__) . '/mmt_export_price.txt';
	
	$connector = mmtConnector::run($feed_from_magento, $feed_from_web, $debug);
	
} catch (Exception $e) {
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}