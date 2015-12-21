DROP TRIGGER IF EXISTS update_stock_status;
delimiter //
CREATE TRIGGER update_stock_status Before UPDATE ON product 
FOR EACH ROW 
BEGIN
  declare $min_in_stock_amount int;
	select value into $min_in_stock_amount from systemsettings where type='min_in_stock_amount';
  if old.stockOnHand = new.stockOnHand then
    -- No change
    set new.statusId = old.statusId;
  elseif new.stockOnHand >= $min_in_stock_amount then
    -- In Stock
    set new.statusId = 2;
  elseif new.stockOnHand >0 then
    -- Low Stock
    set new.statusId = 5;
  else 
    -- Call for ETA
    set new.statusId = 8;
  end if;
	
END;//
