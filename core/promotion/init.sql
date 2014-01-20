-- Setting Up Database
DROP TABLE IF EXISTS `asset`;
CREATE TABLE `asset` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `assetId` varchar(32) NOT NULL DEFAULT '',
    `filename` varchar(100) NOT NULL DEFAULT '',
    `mimeType` varchar(50) NOT NULL DEFAULT '',
    `path` varchar(200) NOT NULL DEFAULT '',
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,UNIQUE INDEX (`assetId`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `courier`;
CREATE TABLE `courier` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL DEFAULT '',
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,INDEX (`name`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `orderNo` varchar(50) NOT NULL DEFAULT '',
    `invNo` varchar(50) NOT NULL DEFAULT '',
    `orderDate` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `totalAmount` Double(10,4) unsigned NOT NULL DEFAULT 0,
    `totalPaid` Double(10,4) unsigned NOT NULL DEFAULT 0,
    `statusId` int(10) unsigned NOT NULL DEFAULT 0,
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`statusId`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,INDEX (`orderNo`)
    ,INDEX (`invNo`)
    ,INDEX (`orderDate`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `orderinfo`;
CREATE TABLE `orderinfo` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `value` varchar(255) NOT NULL DEFAULT '',
    `orderId` int(10) unsigned NOT NULL DEFAULT 0,
    `typeId` int(10) unsigned NOT NULL DEFAULT 0,
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`orderId`)
    ,INDEX (`typeId`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `orderinfotype`;
CREATE TABLE `orderinfotype` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL DEFAULT '',
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,INDEX (`name`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `orderstatus`;
CREATE TABLE `orderstatus` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(32) NOT NULL DEFAULT '',
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
DROP TABLE IF EXISTS `shippment`;
CREATE TABLE `shippment` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `noOfCartons` int(10) unsigned NOT NULL DEFAULT 0,
    `receiver` varchar(50) NOT NULL DEFAULT '',
    `address` varchar(50) NOT NULL DEFAULT '',
    `contact` varchar(50) NOT NULL DEFAULT '',
    `shippingDate` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `conNoteNo` varchar(50) NOT NULL DEFAULT '',
    `estShippingCost` Double(10,4) unsigned NOT NULL DEFAULT 0,
    `deliveryInstructions` varchar(255) NOT NULL DEFAULT '',
    `courierId` int(10) unsigned NOT NULL DEFAULT 0,
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`courierId`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `entityId` int(10) unsigned NOT NULL DEFAULT 0,
    `entityName` varchar(100) NOT NULL DEFAULT '',
    `comments` varchar(255) NOT NULL DEFAULT '',
    `groupId` varchar(100) NOT NULL DEFAULT '',
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,INDEX (`entityId`)
    ,INDEX (`entityName`)
    ,INDEX (`groupId`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `transId` varchar(100) NOT NULL DEFAULT '',
    `type` varchar(100) NOT NULL DEFAULT '',
    `entityId` int(10) unsigned NOT NULL DEFAULT 0,
    `entityName` varchar(100) NOT NULL DEFAULT '',
    `funcName` varchar(100) NOT NULL DEFAULT '',
    `msg` LONGTEXT NOT NULL ,
    `comments` varchar(255) NOT NULL DEFAULT '',
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,INDEX (`transId`)
    ,INDEX (`entityId`)
    ,INDEX (`entityName`)
    ,INDEX (`type`)
    ,INDEX (`funcName`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `firstName` varchar(50) NOT NULL DEFAULT '',
    `lastName` varchar(50) NOT NULL DEFAULT '',
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,INDEX (`firstName`)
    ,INDEX (`lastName`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL DEFAULT '',
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
DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `key` varchar(32) NOT NULL DEFAULT '',
    `data` longtext NOT NULL ,
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,UNIQUE INDEX (`key`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `systemsettings`;
CREATE TABLE `systemsettings` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `type` varchar(50) NOT NULL DEFAULT '',
    `value` varchar(255) NOT NULL DEFAULT '',
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,UNIQUE INDEX (`type`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `useraccount`;
CREATE TABLE `useraccount` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL DEFAULT '',
    `password` varchar(40) NOT NULL DEFAULT '',
    `personId` int(10) unsigned NOT NULL DEFAULT 0,
    `active` bool NOT NULL DEFAULT 1,
    `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
    `createdById` int(10) unsigned NOT NULL DEFAULT 0,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updatedById` int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ,INDEX (`personId`)
    ,INDEX (`createdById`)
    ,INDEX (`updatedById`)
    ,INDEX (`password`)
    ,UNIQUE INDEX (`username`)
) ENGINE=innodb DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `role_useraccount`;
CREATE TABLE `role_useraccount` (
    `roleId` int(10) unsigned NOT NULL,
    `useraccountId` int(10) unsigned NOT NULL,
    `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
    `createdById` int(10) unsigned NOT NULL,
    UNIQUE KEY `uniq_role_useraccount` (`roleId`,`useraccountId`),
    KEY `idx_role_useraccount_roleId` (`roleId`),
    KEY `idx_role_useraccount_useraccountId` (`useraccountId`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

-- Completed CRUD Setup.