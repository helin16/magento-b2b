<?php
require_once 'bootstrap.php';
try {
	echo "Hello<br/>";
	echo '<pre>';

	$url = "http://www.streakwave.com.au/store/ubiquiti-networks/ubiquiti-accessories%20/eth-sp-ubiquiti%20-ethernet-surge-protector-gigabit";

	$page = HTMLParser::getWebsite($url);
	
	$image = $page->find('#image', 0);
	var_dump($image);
	
	echo '</pre>';

} catch (Exception $e)
{ 
	echo "<pre>" . $e->getTraceAsString() . "</pre>";
	throw $e;
}
?>