DROP TABLE IF EXISTS `productprice_history`;
CREATE TABLE `productprice_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productpriceId` int(10) NOT NULL DEFAULT '0',
  `productId` int(10) unsigned NOT NULL DEFAULT '0',
  `typeId` int(10) unsigned NOT NULL DEFAULT '0',
  `oldprice` double(10,4) unsigned NOT NULL DEFAULT '0.0000',
  `start` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `end` datetime NOT NULL DEFAULT '9999-12-31 23:59:59',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `createdById` int(10) unsigned NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL DEFAULT '0001-01-01 00:00:00',
  `updatedById` int(10) unsigned NOT NULL DEFAULT '0',
  `newprice` double(10,4) unsigned NOT NULL DEFAULT '0.0000',
  `hiscreated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `created` (`created`),
  KEY `createdById` (`createdById`),
  KEY `updated` (`updated`),
  KEY `updatedById` (`updatedById`),
  KEY `productpriceId` (`productpriceId`),
  KEY `productId` (`productId`),
  KEY `typeId` (`typeId`),
  KEY `oldprice` (`oldprice`),
  KEY `sl` (`newprice`),
  KEY `start` (`start`),
  KEY `end` (`end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TRIGGER IF EXISTS updateproductpricehistory;
delimiter //
CREATE TRIGGER updateproductpricehistory BEFORE UPDATE ON productprice 
FOR EACH ROW 
BEGIN
  INSERT INTO productprice_history SELECT NULL, h.*, NEW.price, NOW() FROM productprice h WHERE id = OLD.id;
END;//
