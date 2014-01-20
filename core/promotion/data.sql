############################ add role table
insert into `role`(`id`, `name`,`active`, `created`, `createdById`, `updated`, `updatedById`) values 
	(1, 'Logistics', 1, NOW(), 10, NOW(), 10),
	(2, 'Sales', 1, NOW(), 10, NOW(), 10),
	(3, 'Billing', 1, NOW(), 10, NOW(), 10),
	(4, 'Store Manager', 1, NOW(), 10, NOW(), 10),
	(5, 'Administrator', 1, NOW(), 10, NOW(), 10);

############################ add person table
ALTER TABLE `person` AUTO_INCREMENT = 10;
insert into `person`(`id`, `firstName`, `lastName`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(10, 'System', 'User', 1, NOW(), 10, NOW(), 10),
	(1, 'Logistics', 'user', 1, NOW(), 10, NOW(), 10),
	(2, 'Sales', 'user', 1, NOW(), 10, NOW(), 10),
	(3, 'Billing', 'user', 1, NOW(), 10, NOW(), 10),
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
	
############################ add orderinfotype table
insert into `orderinfotype` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'Customer Name', 1, NOW(), 10, NOW(), 10),
	(2, 'Shipping Address', 1, NOW(), 10, NOW(), 10),
	(3, 'Billing Address', 1, NOW(), 10, NOW(), 10),
	(4, 'Shipping PostCode', 1, NOW(), 10, NOW(), 10),
	(5, 'Customer Contact', 1, NOW(), 10, NOW(), 10);


############################ add systemsettings table
insert into `systemsettings`(`type`, `value`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	('b2b_soap_wsdl', 'http://ccbooks.com.au/index.php/api/v2_soap?wsdl=1',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_user', 'B2BUser',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_key', 'B2BUser',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_timezone', 'Australia/Melbourne',  1, NOW(), 10, NOW(), 10),
	('b2b_soap_last_import_time', '2012-01-20 22:24:20',  1, NOW(), 10, NOW(), 10);