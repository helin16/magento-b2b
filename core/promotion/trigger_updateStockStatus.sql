DROP TRIGGER IF EXISTS update_stock_status;
delimiter //
CREATE TRIGGER update_stock_status Before UPDATE ON product 
FOR EACH ROW 
BEGIN
  declare $min_in_stock_amount int;
  declare $supplier_quantity int;
  declare $mel_quantity int;
  declare $other_quantity int;
  select value into $min_in_stock_amount from systemsettings where type='min_in_stock_amount';
  if old.stockOnHand = new.stockOnHand then
    begin
    end;
  else
     begin
       select IFNULL(sum(canSupplyQty),0) into $supplier_quantity from suppliercode where productId = old.id;
       set $mel_quantity  = substr(LPAD(cast($supplier_quantity as char(10)),10,'0'),1,5);
       set $other_quantity = substr(LPAD(cast($supplier_quantity as char(10)),10,'0'),6,5);
       set $mel_quantity = new.stockOnHand + $mel_quantity;
       if $mel_quantity  >= $min_in_stock_amount then
           set new.statusId = 2;
       elseif $mel_quantity >0 then
            set new.statusId = 5;
       elseif  $other_quantity > 0 then
             set new.statusId = 4;
       else
            set new.statusId = 8;
       end if;
     end;
  end if;
END;//
