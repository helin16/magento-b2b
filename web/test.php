<?php
require 'bootstrap.php';

$client = new B2BConnector('http://ccbooks.com.au/index.php/api/v2_soap/?wsdl', 'B2BUser', 'B2BUser');
$result = $client->getOrderInfo('100000002');
var_dump($result);