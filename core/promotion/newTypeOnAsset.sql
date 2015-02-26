ALTER TABLE `asset` ADD `type` VARCHAR(20) NOT NULL DEFAULT '' AFTER `assetId`, ADD INDEX (`type`) ;
update asset set type='PRODUCT_DEC' where filename like 'full_desc_%';
update asset set type='PRODUCT_TMP' where type = ''