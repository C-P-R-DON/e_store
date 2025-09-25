drop procedure if exists create_employee;
drop procedure if exists insert_category;
drop procedure if exists insert_product;
drop procedure if exists log_product_update;
drop trigger if exists product_id_change;
drop trigger if exists product_deletion;
drop procedure if exists checkout;

delimiter //
#a
create procedure create_employee(
	in emp_username char(25),
    in emp_id int,
    in emp_email char(25),
    in temp_pass char(64))
    begin
		insert into employee values(emp_id, emp_email, emp_username, (SHA2(temp_pass, 256)));
        set @create_emp = concat('create user "', emp_username, '"@"%" identified by "', temp_pass, '" password expire');
		prepare create_user_stmt from @create_emp;
        set @emp_usr_select = concat('grant select on * to "', emp_username, '"@"%"');
        prepare grant_emp_select from @emp_usr_select;
        set @emp_usr_exe1 = concat('grant execute on insert_category to "', emp_username, '"@"%"');
        prepare grant_emp_select from @emp_usr_exe1;
        set @emp_usr_exe2 = concat('grant execute on insert_product to "', emp_username, '"@"%"');
        prepare grant_emp_select from @emp_usr_exe2;
        set @emp_usr_exe3 = concat('grant execute on log_prodcut_update to "', emp_username, '"@"%"');
        prepare grant_emp_select from @emp_usr_exe3;
    end //
    
create procedure insert_category(
	in new_description char(120),
    in new_name char(25))
    begin
		insert into category values(
			new_name,
            new_description);
    end //

create procedure insert_product(
	in new_name char(25),
    in new_current_stock int, 
    in new_advised_stock int,
    in new_product_id int,
    in new_product_description char(100),
    in new_price decimal(10,2),
    in new_category_name char(25),
    in new_image char(25))
	begin
		insert into product values(
        new_product_id,
        new_category_name,
        new_name,
        new_product_description,
        new_price,
        new_advised_stock,
        new_current_stock,
        new_image);
    end //
    
create procedure log_product_update(
	in new_emp_id int,
    in new_cust_id int,
    in new_timestamp timestamp,
    in new_action enum("INSERT", "UPDATE", "DELETE"),
    in new_new_price decimal(10,2),
    in new_old_price decimal(10,2),
    in new_new_stock int,
    in new_old_stock int,
    in new_product_id int,
    in new_order_id int,
    in new_updated_by_role enum("EMPLOYEE", "CUSTOMER"))
    begin
    declare new_hist_id int default ((select count(*) from history) + 1);
		insert into history values(
			new_hist_id,
        	new_emp_id,
            new_cust_id,
			new_timestamp,
			new_action,
			new_new_price,
			new_old_price,
			new_new_stock,
			new_old_stock,
			new_product_id,
			new_order_id,
			new_updated_by_role);
    end //

#b
create trigger product_id_change
	before update on product
	for each row
begin
IF !(NEW.product_id <=> OLD.product_id) THEN
	SIGNAL SQLSTATE '45000'
	SET MESSAGE_TEXT = ' The prod id is not allowed to be changed';
END IF;	
end //

create trigger product_deletion
	before delete on product
	for each row
begin
	SIGNAL SQLSTATE '45000'
	SET MESSAGE_TEXT = ' The product is not allowed to be deleted';
end //

#c    
CREATE PROCEDURE checkout(
	IN p_customer_id INT,
	OUT p_order_id INT,
	OUT p_out_of_stock_product INT)
begin
	declare done int default false;
	declare item int;
    declare item_quant int;
    declare item_stock int;
    declare item_price decimal(10,2);
    declare new_order_id int;
	declare cur cursor for (
		select product_id from cart_item
		where cust_id = p_customer_id);
	declare continue handler for not found set done = true;
    start transaction;
    set p_out_of_stock_product = -1;
	set new_order_id = (select count(order_id) from cust_order) + 1;
	insert into cust_order values(
		new_order_id,
		p_customer_id,
		"preparing to ship",
		now(),
		0);
	open cur;
    item_loop: loop
		fetch cur into item;
        if done then 
			leave item_loop;
		end if;
        set item_stock = (select current_stock from product where product_id = item); 
        set item_quant = (select quantity from cart_item where cust_id = p_customer_id and product_id = item); 
        set item_price = (select price from product where product_id = item);
		If (select current_stock from product where product_id = item) >= (item_quant) then
			insert into line_item values (
				new_order_id, 
                item, 
                item_quant,
                item_price);
            update cust_order 
            set total_dollars = total_dollars + item_quant*item_price
			where order_id = new_order_id;
            update product 
            set current_stock = current_stock - item_quant
            where product_id = item;
            call log_product_update(
				null,
				p_customer_id,
				now(),
				"UPDATE",
				null,
				null,
				(item_stock - item_quant),
				item_stock,
				item,
				new_order_id,
				"CUSTOMER");
		Else
			set p_out_of_stock_product = item; 
			rollback;
            leave item_loop;
		end if;
	end loop;
    close cur;
if (p_out_of_stock_product = -1) then
	delete from cart_item where cust_id = p_customer_id;
end if;
set p_order_id = new_order_id;
commit;
end //
    
delimiter ;

select * from product;