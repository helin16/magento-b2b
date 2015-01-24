<?php

require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
$pdf1 = EntityToPDF::getPDF(Order::get(4290), 'docket');
$pdf2 = EntityToPDF::getPDF(PurchaseOrder::get(4));
// We'll be outputting a PDF
// header('Content-Type: application/pdf');
var_dump($pdf1);
var_dump($pdf2);
// The PDF source is in original.pdf
// readfile($pdf)
?>