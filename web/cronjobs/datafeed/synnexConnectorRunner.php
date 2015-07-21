<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/synnexConnector.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

try {
	
	$debug = true;
	$now = str_replace(' ', '_', UDate::now(UDate::TIME_ZONE_MELB)->getDateTimeString());
	$feed_from_magento = dirname(__FILE__) . "/export_product.csv";
	$feed_from_web = dirname(__FILE__) . '/synnex_export_price.txt';
	$feed_from_ftp = dirname(__FILE__) . "/synnex_feed_ftp_2015-07-18.csv";
	
// 	$connector = synnexConnector::run($feed_from_magento, $feed_from_web, $feed_from_ftp, $debug);
	$connector = synnexConnector::getBrandCategoryPairs($feed_from_magento, $feed_from_web, $feed_from_ftp, $debug);
	
} catch (Exception $e) {
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}