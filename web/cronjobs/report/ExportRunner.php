<?php
require_once dirname(__FILE__) . '/class/ExportAbstract.php';

// //run sales export for xero
// require_once dirname(__FILE__) . '/class/SalesExport_Xero.php';
// SalesExport_Xero::run(true);

//run bill export for xero
require_once dirname(__FILE__) . '/class/BillExport_Xero.php';
BillExport_Xero::run(true);
