ALTER TABLE `product` ADD `mageId` varchar(20) NOT NULL DEFAULT '' AFTER `name` , ADD INDEX ( `mageId` );
ALTER TABLE `product` ADD `price` varchar(20) NOT NULL DEFAULT '' AFTER `mageId` , ADD INDEX ( `price` );
ALTER TABLE `product` ADD `stockOnHand` double(10,4) unsigned NOT NULL DEFAULT '0.0000' AFTER `price` , ADD INDEX ( `stockOnHand` );
ALTER TABLE `product` ADD `isFromB2B` tinyint(1) NOT NULL DEFAULT '0' AFTER `stockOnHand` , ADD INDEX ( `isFromB2B` );