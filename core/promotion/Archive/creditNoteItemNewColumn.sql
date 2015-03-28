ALTER TABLE `creditnoteitem` ADD `totalPrice` DOUBLE(10, 4) NOT NULL DEFAULT '0.0000' AFTER `unitCost`;
update creditnoteitem set totalPrice = unitPrice * qty;
ALTER TABLE `creditnote` ADD `shippingValue` DOUBLE(10,4) NOT NULL DEFAULT '0.0000' AFTER `totalPaid`;