insert into `courierinfotype` (`id`, `name`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
(2, 'API KEY', 1, NOW(), 1, NOW(), 1),
(3, 'API_URL', 1, NOW(), 1, NOW(), 1),
(4, 'ACCOUNT_ID', 1, NOW(), 1, NOW(), 1),
(5, 'PARCEL_TRACK_URL', 1, NOW(), 1, NOW(), 1);


insert into `courierinfo` (`courierId`, `typeId`, `value`, `active`, `created`, `createdById`, `updated`, `updatedById`) values
(3, 2, 'bc64a1842ba467a1583f2f1bf9e0264d', 1, NOW(), 1, NOW(), 1),
(3, 3, 'http://farmapi.fastway.org/v2/{method}', 1, NOW(), 1, NOW(), 1),
(3, 4, '20522', 1, NOW(), 1, NOW(), 1),
(3, 5, 'http://www.fastway.com.au/courier-services/track-your-parcel?l={label}', 1, NOW(), 1, NOW(), 1);

ALTER TABLE `shippment` ADD `addressId` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `receiver` ,ADD INDEX ( `addressId` ) ;