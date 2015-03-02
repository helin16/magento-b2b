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
// require_once dirname(__FILE__) . '/class/ItemExport_Xero.php';
// ItemExport_Xero::run(true);

//run ManualJournalExport_Xero export for xero
require_once dirname(__FILE__) . '/class/ManualJournalExport_Xero.php';
ManualJournalExport_Xero::run(true);

//run item list export for xero
require_once dirname(__FILE__) . '/class/ItemExport_Magento.php';
ItemExport_Magento::run(true);

require_once dirname(__FILE__) . '/class/PaymentExport_Xero.php';
PaymentExport_Xero::run(true);