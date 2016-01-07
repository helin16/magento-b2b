-- 334 daily promotion, 339 weekend promotion
-- 335 boxing day promotion, 336 new year promotion
-- 337 Xmas promotion
update productcategory set active = 1 where id in (334, 339,335, 336, 337);

insert into systemsettings (`type`, `value`, `description`, `active`, `created`, `createdById`, `updated`, `updatedById` ) values
    ('is_daily_promotion_time', 0, 'The flag to judge the whether the daily promotion time starts or not', 1, NOW(), 10, NOW(), 10);

insert into productpricetype (`name`, `description`, `needTime`, `active`, `created`, `createdById`, `updated`, `updatedById` ) values
    ('Daily Special Price', 'Promotional purpose sepcial price for every night', 0, 1, NOW(), 10, NOW(), 10);