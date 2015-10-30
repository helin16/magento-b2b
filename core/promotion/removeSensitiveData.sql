UPDATE `bpcinternal`.`customer` SET `email` = 'customer@test.com.au';

UPDATE `bpcinternal`.`supplier` SET `email` = 'supplier@test.com.au';
UPDATE `bpcinternal`.`supplier` SET `contactNo` = '12345678';

UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'b2b_soap_wsdl';
UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'b2b_soap_user';
UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'b2b_soap_key';
UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'sending_server_conf';

TRUNCATE TABLE `bpcinternal`.`useraccount`;
INSERT INTO `bpcinternal`.`useraccount` (`id`, `username`, `password`, `personId`, `active`, `created`, `createdById`, `updated`, `updatedById`) VALUES
(10, '075ae3d2fc31640504f814f60e5ef713', 'disabled', 10, 1, '2014-03-06 19:47:35', 10, '2014-03-06 08:47:35', 10),
(24, 'testuser', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3', 24, 1, '2014-12-20 13:23:44', 20, '2015-10-30 02:53:17', 20);

UPDATE `bpcinternal`.`orderinfo` SET  `value` =  'customer@test.com.au' WHERE  `orderinfo`.`typeId` = 2;

UPDATE `bpcinternal`.`address` SET  `contactNo` =  '12345678';
UPDATE `bpcinternal`.`address` SET  `street` =  '12 Sesame St';
UPDATE `bpcinternal`.`address` SET  `city` =  'Mt Waverley';

UPDATE `bpcinternal`.`shippment` SET  `contact` =  '12345678';
UPDATE `bpcinternal`.`shippment` SET  `conNoteNo` =  'REF-1234-abcd';