ALTER TABLE `orderitem` ADD `unitCost` DOUBLE(10,4) NOT NULL DEFAULT '0.0000' AFTER `margin`, ADD INDEX (`unitCost`) ;
ALTER TABLE `payment` CHANGE `value` `value` DOUBLE(10,4) NOT NULL DEFAULT '0.0000';
ALTER TABLE `payment` ADD `type` VARCHAR(10) NOT NULL DEFAULT '' AFTER `value`, ADD INDEX (`type`) ;
update `payment` set `type`='PAYMENT';

ALTER TABLE `product` ADD `totalRMAValue` DOUBLE(10,4) NOT NULL DEFAULT '0.0000' AFTER `stockInRMA`;
ALTER TABLE `product` DROP INDEX `totalOnHandValue`;
ALTER TABLE `product` DROP INDEX `totalInPartsValue`;
ALTER TABLE `product` DROP INDEX `stockOnOrder_2`;

ALTER TABLE `productqtylog` ADD `totalRMAValue` DOUBLE(10,4) NOT NULL DEFAULT '0.0000' AFTER `stockInRMAVar`;
ALTER TABLE `productqtylog` ADD `totalRMAValueVar` DOUBLE(10,4) NOT NULL DEFAULT '0.0000' AFTER `totalRMAValue`;