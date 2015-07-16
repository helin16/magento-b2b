<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));

try {
	// define file names
	$now = str_replace(' ', '_', UDate::now(UDate::TIME_ZONE_MELB)->getDateTimeString());
	$origin_datafeed_file = dirname(__FILE__) . "/" .'synnex_feed_ori_' . $now . '.csv';
// 	file_put_contents($origin_datafeed_file, "");
	$processed_datafeed_file = dirname(__FILE__) . "/" .'synnex_feed_' . $now . '.csv';
// 	file_put_contents($processed_datafeed_file, "");
	$onbudgetpc_csv= dirname(__FILE__) . "/" . 'export_product.csv';
	
// 	// set up ftp connection
// 	$ftp_server = "ftp.budgetpc.com.au";
// 	$ftp_user_name = "synnex";
// 	$ftp_user_pass = "b2Z]7}i?T^+D";
// 	$server_file = "BUDGETPC_synnex_au.txt";
// 	$conn_id = ftp_connect($ftp_server);
// 	$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
// 	if (ftp_get($conn_id, $origin_datafeed_file, $server_file, FTP_BINARY)) {
// 		echo "Successfully written to" . $origin_datafeed_file . "\n";
// 	} else {
// 		echo "There was a problem\n";
// 	}
// 	ftp_close($conn_id);
	
	$csv = new parseCSV();
	$csv->auto($origin_datafeed_file);
	
	print_r($csv);
	
} catch (Exception $e) {
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
