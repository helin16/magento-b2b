############################ add role table
insert into `role`(`id`, `name`,`active`, `created`, `createdById`, `updated`, `updatedById`) values 
	(1, 'Logistics', 1, NOW(), 100, NOW(), 100),
	(2, 'Sales', 1, NOW(), 100, NOW(), 100),
	(3, 'Billing', 1, NOW(), 100, NOW(), 100),
	(4, 'Store Manager', 1, NOW(), 100, NOW(), 100),
	(5, 'Administrator', 1, NOW(), 100, NOW(), 100);

############################ add person table
insert into `person`(`id`, `firstName`, `lastName`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'Logistics', 'user', 1, NOW(), 100, NOW(), 100),
	(2, 'Sales', 'user', 1, NOW(), 100, NOW(), 100),
	(3, 'Billing', 'user', 1, NOW(), 100, NOW(), 100),
	(4, 'Store Manager', 'user', 1, NOW(), 100, NOW(), 100),
	(5, 'Administrator', 'user', 1, NOW(), 100, NOW(), 100);


############################ add user table
ALTER TABLE `useraccount` AUTO_INCREMENT = 100;
insert into `useraccount`(`id`, `username`, `password`, `personId`, `libraryId`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
	(1, 'luser', sha1('user'), 1, 1, 1, NOW(), 100, NOW(), 100),
	(2, 'suser', sha1('user'), 2, 1, 1, NOW(), 100, NOW(), 100),
	(3, 'buser', sha1('user'), 3, 1, 1, NOW(), 100, NOW(), 100),
	(4, 'smuser', sha1('user'), 4, 1, 1, NOW(), 100, NOW(), 100),
	(5, 'auser', sha1('user'), 5, 1, 1, NOW(), 100, NOW(), 100);

############################ add role_useraccount table
insert into `role_useraccount`(`userAccountId`, `roleId`, `created`, `createdById`) values 
	(1, 1, NOW(), 100),
	(2, 2, NOW(), 100),
	(3, 3, NOW(), 100),
	(4, 4, NOW(), 100),
	(5, 5, NOW(), 100);