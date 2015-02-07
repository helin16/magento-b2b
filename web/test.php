<?php

require_once 'bootstrap.php';
$entityName = isset($_REQUEST['entity']) ? trim($_REQUEST['entity']) : '';
if($entityName === '')
	die('Entity Name is NOT provided!');
$entityId = isset($_REQUEST['entityid']) ? trim($_REQUEST['entityid']) : '';
if($entityId === '')
	die('Entity ID is NOT provided!');
if(($entity = $entityName::get($entityId)) instanceof BaseEntityAbstract)
	die('Invalid ' . $entityName . ' provided: ' . $entityId);
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
$pdf1 = EntityToPDF::getPDF($entity);
header('Content-Type: application/pdf');
// The PDF source is in original.pdf
readfile($pdf)
?>