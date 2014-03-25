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