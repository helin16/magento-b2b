ALTER TABLE `purchaseorder` CHANGE `supplierContactId` `supplierContact` VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE `purchaseorder` ADD `supplierContactNumber` VARCHAR(100) NOT NULL DEFAULT '' AFTER `supplierContact`, ADD INDEX (`supplierContactNumber`) ;
ALTER TABLE purchaseorder DROP INDEX supplierContactId;
ALTER TABLE `purchaseorder` ADD INDEX(`supplierContact`);
