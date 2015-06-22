ALTER TABLE `kit` DROP `rootId`, DROP `parentId`, DROP `path`;
ALTER TABLE `sellingitem` ADD `kitId` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `productId`, ADD INDEX (`kitId`) ;