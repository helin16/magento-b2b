<?php

require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
$pdf1 = EntityToPDF::getPDF(Order::get(10376));
$pdf2 = EntityToPDF::getPDF(Order::get(10376), 'docket');
$pdf3 = EntityToPDF::getPDF(PurchaseOrder::get(4));
// We'll be outputting a PDF
// header('Content-Type: application/pdf');
var_dump($pdf1);
var_dump($pdf2);
var_dump($pdf3);
// The PDF source is in original.pdf
// readfile($pdf)
?>