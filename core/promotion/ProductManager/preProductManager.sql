ALTER TABLE `customer` ADD `isFromB2B` tinyint(1) NOT NULL DEFAULT '0' AFTER `mageId` , ADD INDEX ( `isFromB2B` );

ALTER TABLE `product` ADD `stockOnOrder` int(10) NOT NULL DEFAULT '0' AFTER `stockOnHand` , ADD INDEX ( `stockOnOrder` );
ALTER TABLE `product` ADD `shortDescription` varchar(255) NOT NULL DEFAULT '' AFTER `isFromB2B` , ADD INDEX ( `shortDescription` );
ALTER TABLE `product` ADD `fullDescAssetId` varchar(100) NOT NULL DEFAULT '' AFTER `shortDescription` , ADD INDEX ( `fullDescAssetId` );

ALTER TABLE `product` MODIFY COLUMN `sku`varchar(50) NOT NULL DEFAULT ''; 
ALTER TABLE `product` MODIFY COLUMN `name` varchar(100) NOT NULL DEFAULT ''; 


ALTER TABLE `customer` ADD INDEX ( `created` );
ALTER TABLE `customer` ADD INDEX ( `updated` );
ALTER TABLE `customer` ADD INDEX ( `active` );

insert into systemsettings (`type`, `value`, `description`, `active`, `created`, `createdById`, `updated`, `updatedById`)
values('asset_root_dir', '/var/www/html/contents/', 'The root directory of the assets', 1, NOW(), 1, NOW(), 1);


CREATE TABLE `manufacturer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `mageId`  int(10) unsigned NOT NULL DEFAULT '0',
  `isFromB2B` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `mageId` (`mageId`),
  KEY `isFromB2B` (`isFromB2B`),
  KEY `name` (`name`),
  KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `productcode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL DEFAULT '0',
  `typeId` int(10) unsigned NOT NULL DEFAULT '0',
  `code` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `productId` (`productId`),
  KEY `typeId` (`typeId`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `productcodetype` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `allowMultiple` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `name` (`name`),
  KEY `description` (`description`),
  KEY `allowMultiple` (`allowMultiple`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `productimage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL DEFAULT '0',
  `imageAssetId` varchar(20) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `imageAssetId` (`imageAssetId`),
  KEY `productId` (`productId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `supplier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `mageId`  int(10) unsigned NOT NULL DEFAULT '0',
  `isFromB2B` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `mageId` (`mageId`),
  KEY `isFromB2B` (`isFromB2B`),
  KEY `name` (`name`),
  KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `suppliercode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL DEFAULT '0',
  `supplierId` int(10) unsigned NOT NULL DEFAULT '0',
  `code` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `productId` (`productId`),
  KEY `supplierId` (`supplierId`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `productcategory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `rootId` int(10) unsigned NULL DEFAULT NULL,
  `parentId` int(10) unsigned NULL DEFAULT NULL,
  `position` varchar(255) NOT NULL DEFAULT '',
  `mageId`  int(10) unsigned NOT NULL DEFAULT '0',
  `isFromB2B` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `name` (`name`),
  KEY `rootId` (`rootId`),
  KEY `parentId` (`parentId`),
  KEY `position` (`position`),
  KEY `mageId` (`mageId`),
  KEY `isFromB2B` (`isFromB2B`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


CREATE TABLE `product_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL DEFAULT '0',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `productId` (`productId`),
  KEY `categoryId` (`categoryId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `product` DROP `price`, DROP INDEX `price`;