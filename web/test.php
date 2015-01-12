<?php
require 'bootstrap.php';

echo '<pre>';
$items = XeroConnector_Item::get()->getItems(array('Where' => 'Code.contains("02") OR Code.contains("01")', 'order' => 'ItemID'));
var_dump($items);
echo '</pre>';