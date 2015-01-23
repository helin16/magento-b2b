<?php

require_once 'bootstrap.php';

$pdf = EntityToPDF::getPDF(Order::get(4290));
ob_end_clean();
$pdf->Output('/tmp/' . trim(new UDate()) . '.pdf');
