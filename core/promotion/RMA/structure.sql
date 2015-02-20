DROP TABLE IF EXISTS `rma`;
CREATE TABLE `rma` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`raNo` varchar(12) NOT NULL DEFAULT '',
	`orderId` int(10) unsigned NULL DEFAULT NULL,
	`customerId` int(10) unsigned NOT NULL DEFAULT 0,
	`totalValue` double(10,4) unsigned NOT NULL DEFAULT 0,
	`description` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`orderId`)
	,INDEX (`customerId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`totalValue`)
	,UNIQUE INDEX (`raNo`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `rmaitem`;
CREATE TABLE `rmaitem` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`RMAId` int(10) unsigned NOT NULL DEFAULT 0,
	`orderItemId` int(10) unsigned NULL DEFAULT NULL,
	`productId` int(10) unsigned NOT NULL DEFAULT 0,
	`qty` int(10) unsigned NOT NULL DEFAULT 0,
	`unitCost` double(10,4) unsigned NOT NULL DEFAULT 0,
	`itemDescription` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`RMAId`)
	,INDEX (`orderItemId`)
	,INDEX (`productId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`qty`)
	,INDEX (`unitCost`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `creditnote`;
CREATE TABLE `creditnote` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`creditNoteNo` varchar(12) NOT NULL DEFAULT '',
	`customerId` int(10) unsigned NOT NULL DEFAULT 0,
	`orderId` int(10) unsigned NULL DEFAULT NULL,
	`applyTo` varchar(10) NOT NULL DEFAULT '',
	`applyDate` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`totalValue` double(10,4) unsigned NOT NULL DEFAULT 0,
	`description` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`customerId`)
	,INDEX (`orderId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`applyTo`)
	,INDEX (`applyDate`)
	,UNIQUE INDEX (`creditNoteNo`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `creditnoteitem`;
CREATE TABLE `creditnoteitem` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`creditNoteId` int(10) unsigned NOT NULL DEFAULT 0,
	`orderItemId` int(10) unsigned NULL DEFAULT 0,
	`productId` int(10) unsigned NOT NULL DEFAULT 0,
	`qty` int(10) unsigned NOT NULL DEFAULT 0,
	`unitPrice` double(10,4) unsigned NOT NULL DEFAULT 0,
	`unitCost` double(10,4) unsigned NOT NULL DEFAULT 0,
	`itemDescription` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`creditNoteId`)
	,INDEX (`orderItemId`)
	,INDEX (`productId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`qty`)
	,INDEX (`unitPrice`)
	,INDEX (`unitCost`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

ALTER TABLE `payment` CHANGE `orderId` `orderId` int(10) unsigned NULL DEFAULT NULL;
ALTER TABLE `payment` ADD `creditNoteId` int(10) unsigned NULL DEFAULT NULL AFTER `orderId`, ADD INDEX (`creditNoteId`) ;
