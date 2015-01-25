<?php
require_once dirname(__FILE__) . '/class/ExportAbstract.php';

//run sales export for xero
require_once dirname(__FILE__) . '/class/SalesExport_Xero.php';
SalesExport_Xero::run(true);

//run purchase export for xero
// require_once dirname(__FILE__) . '/class/PurchaseExport_Xero.php';
// PurchaseExport_Xero::run(true);
