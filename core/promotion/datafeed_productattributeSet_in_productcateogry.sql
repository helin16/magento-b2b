ALTER TABLE `productcategory` ADD `productAttributesetId` INT( 10 ) NULL DEFAULT NULL AFTER `mageId` ,
ADD INDEX ( `productAttributesetId` ) ;
