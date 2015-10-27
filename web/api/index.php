<?php
require_once dirname(__FILE__) . '/bootstrap.php';

$service = new APIService(true);
$service->run();
?>