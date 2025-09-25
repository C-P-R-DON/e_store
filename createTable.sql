drop table if exists line_item;
drop table if exists history;
drop table if exists cart_item;
drop table if exists product;
drop table if exists employee;
drop table if exists category;
drop table if exists cust_order;
drop table if exists customer;

create table customer(
	id int,
    username char(25),
    password char(64),
    email char(25),
    shipping_address char(100),
    first_name char(20),
    last_name char(20),
    unique (username),
    unique (email),
    primary key (id));

create table cust_order(
	order_id int,
    id int,
    status ENUM('preparing to ship','in transit','delivered'),
    date timestamp,
    total_dollars decimal(10.2),
    primary key (order_id, id),
    foreign key (id) references customer (id)
    on update cascade 
    on delete cascade); 

create table category(
	name char(25),
    description char(150),
    primary key (name)); 

create table product(
	product_id int,
	category_name char(25),
	name char(25),
    product_description char(100),
    price decimal(10,2),
    advised_stock int,
    current_stock int, 
    image char(25),
    foreign key (category_name) references category(name)
    on update cascade
    on delete cascade,
    primary key (product_id, category_name)); 

create table employee(
	id int,
	email char(25),
    username char(20),
    new_password char(64),
    assigned_password char(64),
    primary key (id)); 
    
create table history(
	hist_id int, 
    emp_id int,
    cust_id int,
    timestamp timestamp,
    action enum("INSERT", "UPDATE", "DELETE"),
    new_price decimal(10,2),
    old_price decimal(10,2),
    new_stock int,
    old_stock int,
    product_id int,
    order_id int,
    updated_by_role enum("EMPLOYEE", "CUSTOMER"),
    foreign key (product_id) references product(product_id)
    on update cascade,
    foreign key  (order_id) references cust_order(order_id)
    on update cascade,
	foreign key (cust_id) references customer(id)
    on update cascade,
    foreign key (emp_id) references employee(id)
    on update cascade,
    primary key (hist_id)); 
    
create table cart_item(
	cust_id int,
    quantity int,
    product_id int,
    foreign key (product_id) references product(product_id)
    on update cascade,
    foreign key (cust_id) references customer(id)
    on update cascade,
    primary key (cust_id,product_id));

create table line_item(
	order_id int,
    product_id int,
    quantity int,
    order_price decimal(10,2),
    foreign key (order_id) references cust_order(order_id)
    on update cascade
    on delete cascade,
    foreign key (product_id) references product (product_id)
    on update cascade
    on delete cascade);

