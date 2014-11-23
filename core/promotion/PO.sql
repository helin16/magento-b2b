ALTER TABLE `purchaseorder` CHANGE `supplierContactId` `supplierContact` VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE `purchaseorder` ADD `supplierContactNumber` VARCHAR(100) NOT NULL DEFAULT '' AFTER `supplierContact`, ADD INDEX (`supplierContactNumber`) ;
ALTER TABLE purchaseorder DROP INDEX supplierContactId;
ALTER TABLE `purchaseorder` ADD INDEX(`supplierContact`);

ALTER TABLE `purchaseorder` ADD `shippingCost` DOUBLE(10,4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `supplierContactNumber`, ADD INDEX (`shippingCost`) ;
ALTER TABLE `purchaseorder` ADD `handlingCost` DOUBLE(10,4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `shippingCost`, ADD INDEX (`shippingCost`) ;

ALTER TABLE `purchaseorderitem` ADD `supplierId` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `supplierItemCode`, ADD INDEX (`supplierId`) ;