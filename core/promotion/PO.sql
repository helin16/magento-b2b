ALTER TABLE `purchaseorder` CHANGE `supplierContactId` `supplierContact` VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE `purchaseorder` ADD `supplierContactNumber` VARCHAR(100) NOT NULL DEFAULT '' AFTER `supplierContact`, ADD INDEX (`supplierContactNumber`) ;
ALTER TABLE purchaseorder DROP INDEX supplierContactId;
ALTER TABLE `purchaseorder` ADD INDEX(`supplierContact`);

ALTER TABLE `purchaseorder` ADD `shippingCost` DOUBLE(10,4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `supplierContactNumber`, ADD INDEX (`shippingCost`) ;
ALTER TABLE `purchaseorder` ADD `handlingCost` DOUBLE(10,4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `shippingCost`, ADD INDEX (`shippingCost`) ;



INSERT INTO `orderinfotype` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) VALUES
(11, 'Est. Shipping Cost', 1, NOW(), 10, NOW(), 10),
(12, 'Est. Package Handling Cost', 1, NOW(), 10, NOW(), 10);

#adding receiving qty to the purchaseorderitem table
ALTER TABLE `purchaseorderitem` ADD `receivedQty` INT(10) NOT NULL DEFAULT '0' AFTER `qty`, ADD INDEX (`receivedQty`) ;

#creating receivingItem table
#DROP TABLE IF EXISTS `receivingitem`;
CREATE TABLE `receivingitem` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`purchaseOrderId` int(10) unsigned NOT NULL DEFAULT 0,
	`productId` int(10) unsigned NOT NULL DEFAULT 0,
	`unitPrice` double(10,4) unsigned NOT NULL DEFAULT 0,
	`serialNo` varchar(10) NOT NULL DEFAULT '',
	`invoiceNo` varchar(10) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`purchaseOrderId`)
	,INDEX (`productId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`serialNo`)
	,INDEX (`unitPrice`)
	,INDEX (`invoiceNo`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

#add ETA column onto purchaseorder
ALTER TABLE `purchaseorder` ADD `eta` DATETIME NOT NULL DEFAULT '0001-01-01 00:00:00' AFTER `orderDate`, ADD INDEX (`eta`) ;

#add email onto supplier
ALTER TABLE `supplier` ADD `email` VARCHAR(100) NOT NULL DEFAULT '' AFTER `contactNo`, ADD INDEX (`email`) ;

#changed Product for qty
ALTER TABLE `product` ADD `stockOnPO` INT(10) NOT NULL DEFAULT '0' AFTER `stockOnOrder`;
ALTER TABLE `product` CHANGE `stockOnHand` `stockOnHand` INT(10) NOT NULL DEFAULT '0';
ALTER TABLE `product` ADD INDEX(`stockOnPO`);
ALTER TABLE `product` ADD INDEX(`stockOnOrder`);

#added SALE role
INSERT INTO `role` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) VALUES
(6, 'Sales', 1, NOW() , 10, NOW() , 10);

#add location and other 2 tables
DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL DEFAULT '',
	`description` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`description`)
	,UNIQUE INDEX (`name`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `preferredlocation`;
CREATE TABLE `preferredlocation` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`locationId` int(10) unsigned NOT NULL DEFAULT 0,
	`productId` int(10) unsigned NOT NULL DEFAULT 0,
	`typeId` int(10) unsigned NOT NULL DEFAULT 0,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`locationId`)
	,INDEX (`productId`)
	,INDEX (`typeId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `preferredlocationtype`;
CREATE TABLE `preferredlocationtype` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL DEFAULT '',
	`description` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`description`)
	,UNIQUE INDEX (`name`)
) ENGINE=innodb DEFAULT CHARSET=utf8;


#add column stockCalculated onto purchaseorderitem
ALTER TABLE `purchaseorderitem` ADD `stockCalculated` TINYINT(1) NOT NULL DEFAULT '0' AFTER `totalPrice`, ADD INDEX (`stockCalculated`) ;

#add column status onto order table
ALTER TABLE `order` ADD `type` VARCHAR(10) NOT NULL DEFAULT '' AFTER `orderNo`, ADD INDEX (`type`) ;

#add column isShipped onto orderitem
ALTER TABLE `orderitem` ADD `isShipped` TINYINT(1) NOT NULL DEFAULT '0' AFTER `isPicked`, ADD INDEX (`isShipped`) ;


DELETE FROM `bpcinternal`.`purchaseorder` WHERE `purchaseorder`.`id` = 1001;
DELETE FROM `bpcinternal`.`purchaseorder` WHERE `purchaseorder`.`id` = 1002;
DELETE FROM `bpcinternal`.`purchaseorder` WHERE `purchaseorder`.`id` = 1003;
DELETE FROM `bpcinternal`.`purchaseorder` WHERE `purchaseorder`.`id` = 1004;
DELETE FROM `bpcinternal`.`purchaseorder` WHERE `purchaseorder`.`id` = 1005;


DROP TABLE IF EXISTS `productqtylog`;
CREATE TABLE `productqtylog` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`productId` int(10) unsigned NULL DEFAULT NULL,
	`stockOnHand` int(10) NOT NULL DEFAULT 0,
	`stockOnOrder` int(10) NOT NULL DEFAULT 0,
	`stockOnPO` int(10) NOT NULL DEFAULT 0,
	`comments` varchar(255) NOT NULL DEFAULT '',
	`entityName` varchar(100) NOT NULL DEFAULT '',
	`entityId` int(10) unsigned NOT NULL DEFAULT 0,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`productId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`name`)
	,INDEX (`entityName`)
	,INDEX (`entityId`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

