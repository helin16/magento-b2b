ALTER TABLE `orderstatus` ADD `mageStatus` VARCHAR( 50 ) NOT NULL AFTER `name` ,
ADD INDEX ( `mageStatus` ); 

update `orderstatus` set mageStatus = 'Pending' where id = 1;
update `orderstatus` set mageStatus = 'Canceled' where id = 2;
update `orderstatus` set mageStatus = 'On Hold' where id = 3;
update `orderstatus` set mageStatus = 'Processing' where id = 4;
update `orderstatus` set mageStatus = 'Processing' where id = 5;
update `orderstatus` set mageStatus = 'Processing' where id = 6;
update `orderstatus` set mageStatus = 'Payment Reviewed' where id = 7;
update `orderstatus` set mageStatus = 'Shipped' where id = 8;

insert into courierinfotype (`name`, `active`, `created`, `createdById`, `updated`, `updatedById`)
VALUES ('api_key', 1, NOW(), 10, NOW(), 10);

insert into courierinfo (`courierId`, `value`, `typeId`, `active`, `created`, `createdById`, `updated`, `updatedById`)
VALUES 
    (3, 'http://api.fastway.org/v3/psc/', 1, 1, NOW(),10, NOW(), 10),
    (3, 'xxx', 2, 1, NOW(), 10, NOW(), 10)    ;
    
insert into courierinfotype (`name`, `active`, `created`, `createdById`, `updated`, `updatedById`)
VALUES ('countryCodes', 1, NOW(), 10, NOW(), 10);    
    
insert into courierinfo (`courierId`, `value`, `typeId`, `active`, `created`, `createdById`, `updated`, `updatedById`)
VALUES (3, '[{"Australia": "1"}, {"New Zealand": "6"}, {"Ireland": "11"}, {"N.Ireland": 11}, {"South Africa": 24}]', 3, 1, NOW(), 10, NOW(), 10);    