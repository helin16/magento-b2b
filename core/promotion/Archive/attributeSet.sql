DROP TABLE IF EXISTS `productattribute`;
CREATE TABLE `productattribute` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`code` varchar(100) NOT NULL DEFAULT '',
	`type` varchar(100) NOT NULL DEFAULT '',
	`required` bool NOT NULL DEFAULT 0,
	`scope` varchar(100) NOT NULL DEFAULT '',
	`description` varchar(255) NOT NULL DEFAULT '',
	`mageId` int(10) unsigned NOT NULL DEFAULT 0,
	`isFromB2B` bool NOT NULL DEFAULT 0,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`isFromB2B`)
	,INDEX (`mageId`)
	,UNIQUE INDEX (`code`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `productattributeset`;
CREATE TABLE `productattributeset` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL DEFAULT '',
	`description` varchar(255) NOT NULL DEFAULT '',
	`mageId` int(10) unsigned NOT NULL DEFAULT 0,
	`isFromB2B` bool NOT NULL DEFAULT 0,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`isFromB2B`)
	,INDEX (`mageId`)
	,UNIQUE INDEX (`name`)
) ENGINE=innodb DEFAULT CHARSET=utf8;