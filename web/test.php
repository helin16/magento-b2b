<?php

require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
$pdf = EntityToPDF::getPDF(Order::get(4290));
// We'll be outputting a PDF
header('Content-Type: application/pdf');

// The PDF source is in original.pdf
readfile($pdf)
?>