ALTER TABLE `orderstatus` ADD `mageStatus` VARCHAR( 50 ) NOT NULL AFTER `name` ,
ADD INDEX ( `mageStatus` ); 