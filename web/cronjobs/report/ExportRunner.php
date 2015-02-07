<?php
require_once dirname(__FILE__) . '/class/ExportAbstract.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
// //run sales export for xero
require_once dirname(__FILE__) . '/class/SalesExport_Xero.php';
SalesExport_Xero::run(true);

//run bill export for xero
require_once dirname(__FILE__) . '/class/BillExport_Xero.php';
BillExport_Xero::run(true);

//run item list export for xero
require_once dirname(__FILE__) . '/class/ItemExport_Xero.php';
ItemExport_Xero::run(true);
