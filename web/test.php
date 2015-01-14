<?php
require 'bootstrap.php';

echo '<pre>';
$items = XeroConnector_Item::get()->getItems(array('Where' => 'Code.contains("02") OR Code.contains("01")', 'order' => 'ItemID'));
var_dump($items);
echo '</pre>';

echo '<pre>';
$items = XeroConnector_Account::get()->getAccounts(array('where' => array('AccountID' => array('operator' => '==', 'value' => '123'), 
																		  'Status' => array('operator' => '==', 'value' => 'someting...')), 
														 'order' => 'AccountID'
														), "AND");
var_dump($items);
echo '</pre>';