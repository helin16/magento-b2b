<?php
require_once 'bootstrap.php';

$costArray = Order::getByOrderNo('BPC00030167')->getInfo(OrderInfoType::ID_MAGE_ORDER_SHIPPING_COST);
var_dump($costArray);