ALTER TABLE `product` ADD `totalInPartsValue` DOUBLE(10,4) NOT NULL DEFAULT '0' AFTER `totalOnHandValue`, ADD INDEX (`totalInPartsValue`) ;
ALTER TABLE `product` ADD `stockInParts` INT(10) NOT NULL DEFAULT '0' AFTER `stockOnPO`, ADD INDEX (`stockInParts`) ;

ALTER TABLE `product` CHANGE `invenAccNo` `assetAccNo` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `product` DROP INDEX `invenAccNo`;
ALTER TABLE `product` ADD INDEX(`assetAccNo`);
ALTER TABLE `product` ADD `revenueAccNo` VARCHAR(10) NULL DEFAULT '' AFTER `assetAccNo`, ADD INDEX (`revenueAccNo`) ;
ALTER TABLE `product` CHANGE `assetAccNo` `assetAccNo` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `product` ADD `costAccNo` VARCHAR(10) NOT NULL DEFAULT '' AFTER `revenueAccNo`, ADD INDEX (`costAccNo`) ;

ALTER TABLE `product` ADD `stockInRMA` INT(10) NOT NULL DEFAULT '0' AFTER `stockInParts`, ADD INDEX (`stockInRMA`) ;
