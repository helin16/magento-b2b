############################ add role table
insert into `role`(`id`, `name`,`active`, `created`, `createdById`, `updated`, `updatedById`) values 
	(1, 'Warehouse', 1, NOW(), 10, NOW(), 10),
	(2, 'Purchasing', 1, NOW(), 10, NOW(), 10),
	(3, 'Accounting', 1, NOW(), 10, NOW(), 10),
	(4, 'Store Manager', 1, NOW(), 10, NOW(), 10),
	(5, 'Administrator', 1, NOW(), 10, NOW(), 10);

############################ add person table
ALTER TABLE `person` AUTO_INCREMENT = 10;
insert into `person`(`id`, `firstName`, `lastName`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(10, 'System', 'User', 1, NOW(), 10, NOW(), 10),
	(1, 'Logistics', 'user', 1, NOW(), 10, NOW(), 10),
	(2, 'Purchasing', 'user', 1, NOW(), 10, NOW(), 10),
	(3, 'Accounting', 'user', 1, NOW(), 10, NOW(), 10),
	(4, 'Store Manager', 'user', 1, NOW(), 10, NOW(), 10),
	(5, 'Administrator', 'user', 1, NOW(), 10, NOW(), 10);


############################ add user table
ALTER TABLE `useraccount` AUTO_INCREMENT = 10;
insert into `useraccount`(`id`, `username`, `password`, `personId`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(10, md5('disabled'), 'disabled', 10, 1, NOW(), 10, NOW(), 10),
	(1, 'luser', sha1('user'), 1,  1, NOW(), 10, NOW(), 10),
	(2, 'suser', sha1('user'), 2,  1, NOW(), 10, NOW(), 10),
	(3, 'buser', sha1('user'), 3,  1, NOW(), 10, NOW(), 10),
	(4, 'smuser', sha1('user'), 4, 1, NOW(), 10, NOW(), 10),
	(5, 'auser', sha1('user'), 5, 1, NOW(), 10, NOW(), 10);

############################ add role_useraccount table
insert into `role_useraccount`(`userAccountId`, `roleId`, `created`, `createdById`) values 
	(1, 1, NOW(), 10),
	(2, 2, NOW(), 10),
	(3, 3, NOW(), 10),
	(4, 4, NOW(), 10),
	(5, 5, NOW(), 10);
	
############################ add orderstatus table
insert into `orderstatus` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'NEW', 1, NOW(), 10, NOW(), 10),
	(2, 'CANCELLED', 1, NOW(), 10, NOW(), 10),
	(3, 'ON HOLD', 1, NOW(), 10, NOW(), 10),
	(4, 'ETA', 1, NOW(), 10, NOW(), 10),
	(5, 'STOCK CHECKED BY PURCHASING', 1, NOW(), 10, NOW(), 10),
	(6, 'INSUFFICIENT STOCK', 1, NOW(), 10, NOW(), 10),
	(7, 'PICKED', 1, NOW(), 10, NOW(), 10),
	(8, 'SHIPPED', 1, NOW(), 10, NOW(), 10);
	
############################ add orderinfotype table
insert into `orderinfotype` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'Customer Name', 1, NOW(), 10, NOW(), 10),
	(2, 'Customer Email', 1, NOW(), 10, NOW(), 10),
	(3, 'Total Order Qty', 1, NOW(), 10, NOW(), 10),
	(4, 'Magento Order Status', 1, NOW(), 10, NOW(), 10),
	(5, 'Magento Order State', 1, NOW(), 10, NOW(), 10),
	(6, 'Magento Payment Method', 1, NOW(), 10, NOW(), 10);
	
############################ add courierinfotype table
insert into `courierinfotype` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'Url', 1, NOW(), 10, NOW(), 10);
	
############################ add courier table
insert into `courier` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'AUS POST', 1, NOW(), 10, NOW(), 10),
	(2, 'VIC FAST', 1, NOW(), 10, NOW(), 10);
	
############################ add courierinfo table
insert into `courierinfo` (`courierId`, `typeId`, `value`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 1, 'HTTP://auspost.com.au/', 1, NOW(), 10, NOW(), 10),
	(2, 1, 'HTTP://vicfast.com.au/', 1, NOW(), 10, NOW(), 10);


############################ add systemsettings table
insert into `systemsettings`(`type`, `value`, `description`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	('b2b_soap_wsdl', 'http://ccbooks.com.au/index.php/api/v2_soap?wsdl=1', 'Where the magento wsdl v2 is?',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_user', 'B2BUser', 'The user for the magento B2B',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_key', 'B2BUser', 'The user for the magento API key',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_timezone', 'Australia/Melbourne', 'The timezone the magento is operating on',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_last_import_time', '2012-01-20 22:24:20', 'When did we do the imports from Magento last time',  1, NOW(), 10, NOW(), 10),
	('system_timezone', 'Australia/Melbourne', 'The timezone this CURRENT SYSTEM is operating on',  1, NOW(), 10, NOW(), 10);