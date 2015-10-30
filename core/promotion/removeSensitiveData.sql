UPDATE `bpcinternal`.`customer` SET `email` = 'customer@test.com.au';
UPDATE `bpcinternal`.`supplier` SET `email` = 'supplier@test.com.au';
UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'b2b_soap_wsdl';
UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'b2b_soap_user';
UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'b2b_soap_key';
UPDATE `bpcinternal`.`systemsettings` SET  `value` =  '' WHERE  `systemsettings`.`type` like 'sending_server_conf';
DELETE FROM `bpcinternal`.`useraccount` WHERE  (`useraccount`.`id` != 10 and `useraccount`.`username` != 'helin16');
UPDATE `bpcinternal`.`useraccount` SET  `username` =  'testuser', `password` = SHA1(  'test' ) WHERE  `useraccount`.`username` = 'helin16';
