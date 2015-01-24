DROP TABLE IF EXISTS `productqtylog`;
CREATE TABLE `productqtylog` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`productId` int(10) unsigned NULL DEFAULT NULL,
	`stockOnHand` int(10) NOT NULL DEFAULT 0,
	`stockOnHandVar` int(10) NOT NULL DEFAULT 0,
	`totalOnHandValue` double(10,4) unsigned NOT NULL DEFAULT 0,
	`totalOnHandValueVar` double(10,4) unsigned NOT NULL DEFAULT 0,
	`totalInPartsValue` double(10,4) unsigned NOT NULL DEFAULT 0,
	`totalInPartsValueVar` double(10,4) unsigned NOT NULL DEFAULT 0,
	`stockOnOrder` int(10) NOT NULL DEFAULT 0,
	`stockOnOrderVar` int(10) NOT NULL DEFAULT 0,
	`stockOnPO` int(10) NOT NULL DEFAULT 0,
	`stockOnPOVar` int(10) NOT NULL DEFAULT 0,
	`stockInParts` int(10) NOT NULL DEFAULT 0,
	`stockInPartsVar` int(10) NOT NULL DEFAULT 0,
	`stockInRMA` int(10) NOT NULL DEFAULT 0,
	`stockInRMAVar` int(10) NOT NULL DEFAULT 0,
	`comments` varchar(255) NOT NULL DEFAULT '',
	`entityName` varchar(100) NOT NULL DEFAULT '',
	`entityId` int(10) unsigned NOT NULL DEFAULT 0,
	`type` varchar(2) NOT NULL DEFAULT '',
	`active` bool NOT NULL DEFAULT 1,
	`created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
	`createdById` int(10) unsigned NOT NULL DEFAULT 0,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`updatedById` int(10) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
	,INDEX (`productId`)
	,INDEX (`createdById`)
	,INDEX (`updatedById`)
	,INDEX (`entityName`)
	,INDEX (`type`)
) ENGINE=innodb DEFAULT CHARSET=utf8;