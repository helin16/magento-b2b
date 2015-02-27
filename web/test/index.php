<?php
require_once dirname(__FILE__) . '/src/Command.php';
require_once dirname(__FILE__) . '/src/Pdf.php';

use mikehaertl\wkhtmlto\Pdf;

// You can pass a filename, a HTML string or an URL to the constructor
$pdf = new Pdf('http://google.com.au');

// On some systems you may have to set the binary path.
// $pdf->binary = 'C:\...';

$pdf->saveAs('/tmp/new.pdf');