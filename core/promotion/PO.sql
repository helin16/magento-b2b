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