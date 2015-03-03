<?php
require_once 'bootstrap.php';

$costArray = Order::getByOrderNo('BPC00030167')->getInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_COST);
var_dump($costArray);
$cost = StringUtilsAbstract::getValueFromCurrency($costArray[0]):
var_dump($cost);