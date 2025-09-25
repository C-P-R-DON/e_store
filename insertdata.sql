delete from line_item;
delete from history;
delete from cart_item;
delete from product;
delete from employee;
delete from category;
delete from cust_order;
delete from customer;


insert into customer values
(10001, 'MonstroElisasue',  SHA2('matrix', 256), 'esparkle@mubi.com', '1 Newburgh St. London, Westminster W1F 7RB, Great Britai', 'Elizabeth', 'Sparkle'),
(20001, 'redcap', SHA2('simony', 256), 'jtrembley@fn.com', '150 West 22nd St. 9th Floor New York City, New York 10017, United States', 'Joseph', 'Tremblay'),
(30001, 'muadD’ib', SHA2('Arakkis', 256), 'patreides@lp.com', '2900 West Alameda Ave. Burbank, California 91505, United States', 'Paul', 'Atreides');

insert into cust_order values
(1, 20001,'delivered','2025-01-03 20:48:27',35),
(2, 30001,'delivered','2025-01-12 11:47:55',9),
(3, 30001,'delivered','2025-01-16 18:40:09',25),
(4, 20001,'delivered','2025-01-26 03:22:10',380),
(5, 30001,'in transit','2025-02-02 06:36:18',6),
(6, 10001,'in transit','2025-02-21 08:15:45',6),
(7, 20001,'in transit','2025-03-01 19:05:30',45),
(8, 10001,'in transit','2025-03-05 22:49:42',10),
(9, 10001,'preparing to ship','2025-03-08 14:30:15',150),
(10, 20001,'preparing to ship','2025-03-10 12:53:06',300);

insert into category values
('clothing', 'articles of clothing'),
('food', 'consumable items (includes drinks)'),
('furniture', 'articles for the furnishment of rooms'),
('cookware', 'articles to perform cooking duties');

insert into employee values
(1001, 'jeffw@greendale.edu', 'JeffW', null, SHA2('Columbia', 256)),
(1002, 'brittap@greendale.edu', 'BrittaP', null, SHA2('Bagel', 256));

insert into product values
(100000,'clothing','t-shirt','unisex medium size cotton t-shirt (white)',10,55,54,'100000.png'), 
(100001,'clothing','leather belt','one size fits all leather belt',15,25,18,'100001.png'),
(200000,'food','potato chips','1oz single serving bag of potato chips',2,30,30,'200000.jpeg'),
(200001,'food','penne noodles','16oz box penne noodles',2,100,73,'200001.jpeg'),
(200002,'food','ground beef','16oz uncooked ground 80/20 beef',7,25,22,'200002.jpeg'),
(300000,'furniture','swivel chain','black leather office swivel chair',150,10,9,'300000.jpeg'),
(300001,'furniture','couch','green chenille couch 82”x36”x32”',400,8,8,'300001.jpeg'),
(300002,'furniture','mattress','full size 12” thickness memory foam mattress',300,12,9,'300002.jpeg'),
(400000,'cookware','pot','stainless steel cooking pot',25,32,27,'400000.jpeg'),
(400001,'cookware','pan','stainless steel cooking pan',20,30,24,'400001.jpeg');

insert into cart_item values
(20001,2,200001),
(20001,1,200002),
(30001,1,300002),
(30001,3,400001);

insert into history values
(11, 1001,null,'2024-11-23 14:22:12','UPDATE',20,15,null,null,400001,null,'EMPLOYEE'),
(12, 1002,null,'2024-12-05 18:43:43','UPDATE',2,1.5,null,null,200001,null,'EMPLOYEE'),
(13, 1001,null,'2024-12-26 07:16:23','UPDATE',400,380,null,null,300001,null,'EMPLOYEE');


insert into line_item values
(1,100000,2,10),
(1,100001,1,15),
(2,200001,1,2),
(2,200002,1,7),
(3,400000,1,25),
(4,300001,1,380),
(5,200001,3,2),
(6,200000,3,2),
(7,400000,1,25),
(7,400001,1,20),
(8,100000,1,10),
(9,300000,1,150),
(10,300002,1,300);


select * from line_item;
select * from history;
select * from cart_item;
select * from product;
select * from employee;
select * from category;
select * from cust_order;
select * from customer;



