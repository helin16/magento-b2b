DROP PROCEDURE IF EXISTS DailyPromotionOpen;
DROP PROCEDURE IF EXISTS DailyPromotionClose;
DROP PROCEDURE IF EXISTS WeekendPromotionOpen;
DROP PROCEDURE IF EXISTS WeekendPromotionClose;
DROP EVENT IF EXISTS e_DailyPromotionStart;
DROP EVENT IF EXISTS e_DailyPromotionEnd;
DROP EVENT IF EXISTS e_WeekendPromotionStart;
DROP EVENT IF EXISTS e_WeekendPromotionEnd;

SET GLOBAL event_scheduler = ON;

delimiter //
CREATE PROCEDURE DailyPromotionOpen()
BEGIN
  -- open promotion at certain time every day 
  -- no need to open daily promotion on saturday and sunday
  -- 1 is Sunday and 7 is Saturday
  declare $dayOfWeek int;
  SELECT DAYOFWEEK(DATE_ADD(now(), INTERVAL 11 HOUR)) into $dayOfWeek;
  if ($dayOfWeek = 1 or $dayOfWeek = 7) then
    begin
    end;
  else
    update systemsettings set value = 1 where type = 'is_daily_promotion_time';
    UPDATE productprice pp
    INNER JOIN product_category pc
       ON pp.productId = pc.productId
    INNER JOIN product p
       ON pp.productId = p.id
    SET pp.updated = now()
    WHERE pp.typeId = 5 and pp.active = 1 and p.active=1 and pc.active=1 and pc.categoryId = 334;
  end if;
END;

CREATE PROCEDURE DailyPromotionClose()
BEGIN
  -- close daily promotion
  -- no need to close daily promotion on Sunday and Monday
  -- because we do not open daily promortion on Saturday and Sunday
  -- 1 is Sunday and 7 is Saturday
  declare $dayOfWeek int;
  SELECT DAYOFWEEK(DATE_ADD(now(), INTERVAL 11 HOUR)) into $dayOfWeek;
  if ($dayOfWeek = 1 or $dayOfWeek = 7) then
    begin
    end;
  else
    update systemsettings set value = 0 where type = 'is_daily_promotion_time';
    UPDATE productprice pp
    INNER JOIN product_category pc
       ON pp.productId = pc.productId
    INNER JOIN product p
       ON pp.productId = p.id
    SET pp.updated = now()
    WHERE pp.typeId = 5 and pp.active = 1 and p.active=1 and pc.active=1 and pc.categoryId = 334;
  end if;
END;


CREATE PROCEDURE WeekendPromotionOpen()
BEGIN
  -- open promotion at 00:00:00 every Saturday 
  -- 7 is Saturday
  declare $dayOfWeek int;
  SELECT DAYOFWEEK(DATE_ADD(now(), INTERVAL 11 HOUR)) into $dayOfWeek;
  if ($dayOfWeek = 7) then
    update systemsettings set value = 1 where type = 'is_weekend_promotion_time';
    UPDATE productprice pp
    INNER JOIN product_category pc
       ON pp.productId = pc.productId
    INNER JOIN product p
       ON pp.productId = p.id
    SET pp.updated = now()
    WHERE pp.typeId = 6 and pp.active = 1 and p.active=1 and pc.active=1 and pc.categoryId = 339;
  end if;
END;

CREATE PROCEDURE WeekendPromotionClose()
BEGIN
  -- close weekend promotion at 23:55:00 every Sunday
  -- 1 is Sunday
  declare $dayOfWeek int;
  SELECT DAYOFWEEK(DATE_ADD(now(), INTERVAL 11 HOUR)) into $dayOfWeek;
  if ($dayOfWeek = 1) then
    update systemsettings set value = 0 where type = 'is_weekend_promotion_time';
    UPDATE productprice pp
    INNER JOIN product_category pc
       ON pp.productId = pc.productId
    INNER JOIN product p
       ON pp.productId = p.id
    SET pp.updated = now()
    WHERE pp.typeId = 6 and pp.active = 1 and p.active=1 and pc.active=1  and pc.categoryId = 339;
  end if;
END;

-- Note the time difference(11 hours) between Melbourne time and UTC time
-- MySQL server uses UTC time
CREATE EVENT e_DailyPromotionStart ON SCHEDULE
EVERY 1 DAY STARTS '2016-01-01 08:55:00'
ON COMPLETION PRESERVE
ENABLE
COMMENT 'promotion starts 19:55:00(Melbourne time) every night'
DO BEGIN
  call DailyPromotionOpen();
END;


CREATE EVENT e_DailyPromotionEnd ON SCHEDULE
EVERY 1 DAY STARTS '2016-01-02 12:55:00'
ON COMPLETION PRESERVE
ENABLE
COMMENT 'promotion ends at 23:55:00(Melbourne time) every midnight'
DO BEGIN
  call DailyPromotionClose();
END;

-- weekend promotion starts from 00:00:00(Melbourne time)  every Saturday
-- MySQL server uses UTC time, so need to adjust time
-- We use Melbourne time so need to minus 11 hours
CREATE EVENT e_WeekendPromotionStart ON SCHEDULE
EVERY 1 WEEK STARTS CONCAT(CURRENT_DATE + INTERVAL 4 - WEEKDAY(CURRENT_DATE) DAY,' 13:00:00')
ON COMPLETION PRESERVE
ENABLE
COMMENT 'weekend promotion starts 00:00:00(Melbourne time) every Saturday'
DO BEGIN
  call WeekendPromotionOpen();
END;

-- weekend promotion ends from 23:55:00 (Melbourne time) every Sunday
CREATE EVENT e_WeekendPromotionEnd ON SCHEDULE
EVERY 1 WEEK STARTS CONCAT(CURRENT_DATE + INTERVAL 6 - WEEKDAY(CURRENT_DATE) DAY,' 12:55:00')
ON COMPLETION PRESERVE
ENABLE
COMMENT 'weekend promotion ends at 23:55:00(Melbourne time) every Sunday'
DO BEGIN
  call WeekendPromotionClose();
END;

//


