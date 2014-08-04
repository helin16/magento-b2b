CREATE TABLE `customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `contactNo` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `billingAddressId`  int(10) unsigned NOT NULL DEFAULT '0',
  `shippingAddressId`  int(10) unsigned NOT NULL DEFAULT '0',
  `mageId`  int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `createdById` (`createdById`),
  KEY `updatedById` (`updatedById`),
  KEY `billAddressId` (`billingAddressId`),
  KEY `shippingAddress` (`shippingAddressId`),
  KEY `mageId` (`mageId`),
  KEY `name` (`name`),
  KEY `contactNo` (`contactNo`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `order` ADD `customerId` int(10) unsigned NOT NULL DEFAULT '0' AFTER `orderDate` , ADD INDEX ( `customerId` );