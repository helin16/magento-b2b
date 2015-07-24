<?php
require_once 'bootstrap.php';
try {
	echo "Begin" . "\n";
	Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
	Dao::beginTransaction();
	
	//anyware
// 	$since = UDate::now()->modify('-7 day');
// 	$content = MailFetcher::fetchLastestAttachment('mail.budgetpc.com.au', 995, 'marketing@budgetpc.com.au', '03k12jd2WZ9703p', 'FROM "promotions@anyware.com.au" SINCE "' . trim($since->format('d M Y')) . '"');
// 	var_dump($content);

	//mittoni
// 	curl --header 'Host: www.mittoni.com.au' --header 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:39.0) Gecko/20100101 Firefox/39.0' --header 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' --header 'Accept-Language: en-AU,zh-CN;q=0.7,zh-TW;q=0.3' --header 'DNT: 1' --header 'Referer: https://www.mittoni.com.au/account.php' --header 'Cookie: cookie_test=please_accept_for_session; sid=037143676a2b587c95d981a4352cff3a; layout=classic' --header 'Connection: keep-alive' 'https://www.mittoni.com.au/download_price.php?f=Mittoni_Pricelist.csv' -o 'Mittoni_Pricelist.csv' -L

	$cookieFile = fopen(dirname(__FILE__) . "/mittoni.cookies", 'w+');
	fclose($cookieFile);
	$localFile = dirname(__FILE__) . '/mittoni.csv';
	$url = 'https://www.mittoni.com.au/download_price.php?f=Mittoni_Pricelist.csv';
	$data = array(
		'email_address' => 'sales@budgetpc.com.au',
		'password' => 'budgetpc',
		'x' => 26,
		'y'=>10
	);
	$extraOpts = array(
			CURLOPT_HEADER => false,
			CURLOPT_NOBODY => false,
			CURLOPT_URL => 'https://www.mittoni.com.au/login.php/action/process'
	);
	$result = ComScriptCURL::readUrl($url, null, $data, 'POST', $extraOpts);
	var_dump($result);
	
	Dao::commitTransaction();
} catch (Exception $e)
{ 
	echo "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
	Dao::rollbackTransaction();
	throw $e;
}
?>