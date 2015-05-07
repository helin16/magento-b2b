DROP TABLE IF EXISTS `kit`;
CREATE TABLE `kit` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`taskId` int(10) unsigned NULL DEFAULT NULL,
	`productId` int(10) unsigned NULL DEFAULT NULL,
	`barcode` varchar(50) NOT NULL DEFAULT '',
	`soldToCustomerId` int(10) unsigned NULL DEFAULT NULL,
	`soldDate` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`soldOnOrderId` int(10) unsigned NULL DEFAULT NULL,
	`shippmentId` int(10) unsigned NULL DEFAULT NULL,
	`cost` Double(10,4) unsigned NOT NULL DEFAULT 0,
	`price` Double(10,4) unsigned NOT NULL DEFAULT 0,
	`rootId` int(10) unsigned NULL DEFAULT NULL,
	`parentId` int(10) unsigned NULL DEFAULT NULL,
	`path` varchar(255) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`taskId`)
	,INDEX (`productId`)
	,INDEX (`soldToCustomerId`)
	,INDEX (`soldOnOrderId`)
	,INDEX (`shippmentId`)
	,INDEX (`rootId`)
	,INDEX (`parentId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`soldDate`)
	,UNIQUE INDEX (`barcode`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `kitcomponent`;
CREATE TABLE `kitcomponent` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`kitId` int(10) unsigned NOT NULL DEFAULT 0,
	`componentId` int(10) unsigned NOT NULL DEFAULT 0,
	`qty` int(10) unsigned NOT NULL DEFAULT 0,
	`unitCost` double(10,4) unsigned NOT NULL DEFAULT 0,
	`unitPrice` double(10,4) unsigned NOT NULL DEFAULT 0,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`kitId`)
	,INDEX (`componentId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`fromEntityName` varchar(50) NOT NULL DEFAULT '',
	`fromEntityId` int(10) unsigned NOT NULL DEFAULT 0,
	`statusId` int(10) unsigned NOT NULL DEFAULT 0,
	`technicianId` int(10) unsigned NULL DEFAULT NULL,
	`dueDate` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`instructions` text NOT NULL ,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`statusId`)
	,INDEX (`technicianId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`fromEntityName`)
	,INDEX (`fromEntityId`)
	,INDEX (`dueDate`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `taskstatus`;
CREATE TABLE `taskstatus` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(20) NOT NULL DEFAULT '',
	`description` text NOT NULL ,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,UNIQUE INDEX (`name`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

ALTER TABLE `product` ADD `isKit` TINYINT(1) NOT NULL DEFAULT '0' AFTER `fullDescAssetId`, ADD INDEX (`isKit`) ;

insert into `taskstatus` values
(1, 'NEW', 'NEW', 1, NOW(), 10, NOW(), 10),
(2, 'WIP', 'Work In Progress', 1, NOW(), 10, NOW(), 10),
(3, 'FINISHED', 'FINISHED', 1, NOW(), 10, NOW(), 10),
(4, 'ON_HOLD', 'ON_HOLD', 1, NOW(), 10, NOW(), 10),
(5, 'CANCELED', 'CANCELED', 1, NOW(), 10, NOW(), 10);

insert into `role` values
(7, 'Workshop', 1, NOW(), 10, NOW(), 10);
