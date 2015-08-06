ALTER TABLE `productcategory` ADD `productAttributesetId` INT( 10 ) UNSIGNED NULL DEFAULT NULL AFTER `mageId` ,
ADD INDEX ( `productAttributesetId` ) ;
