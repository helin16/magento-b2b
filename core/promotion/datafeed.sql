DROP TABLE IF EXISTS `supplierdatefeedrule`;
CREATE TABLE `supplierdatefeedrule` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`supplierId` int(10) unsigned NOT NULL DEFAULT 0,
	`manufacturerId` int(10) unsigned NULL DEFAULT NULL,
	`categoryId` int(10) unsigned NULL DEFAULT NULL,
	`priceMatchRuleId` int(10) unsigned NULL DEFAULT NULL,
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`supplierId`)
	,INDEX (`manufacturerId`)
	,INDEX (`categoryId`)
	,INDEX (`priceMatchRuleId`)
	,INDEX (`supplierId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
) ENGINE=innodb DEFAULT CHARSET=utf8;