<?php
// connect to database
function connectDB()
{
 $config = parse_ini_file("/local/my_web_files/cdonahue/db.ini");
 $dbh = new PDO($config['dsn'], $config['username'], $config['password']);
 $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 return $dbh;
}
// retrieves the product categories from the database 
function get_categories() {
	try {
	   $dbh = connectDB();
	   $dbh->beginTransaction();
	   $statement = $dbh->prepare("SELECT * FROM category ");
	   $result = $statement->execute();
	   $row=$statement->fetchAll();
	   $dbh->commit();
	   $dbh=null;
	   return $row;
	}catch (PDOException $e) {
	   print "Error!" . $e->getMessage() . "<br/>";
	   die();
	}
}
// retrieves the products of a give category from the database 
function get_products($category) {
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT * FROM product WHERE category_name = :category");
		$statement->bindParam(":category", $category);
		$result = $statement->execute();
		$row=$statement->fetchAll();
		$dbh->commit();
		$dbh=null;
		return $row;
	}catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}
// verifies that login infomration matches customer information in the database
function authenticate_cust($user, $passwd) {
	try {
	   $dbh = connectDB();
	   $dbh->beginTransaction();
	   $statement = $dbh->prepare("SELECT count(*) FROM customer ".
	   "where username = :username and password = sha2(:passwd,256) ");
	   $statement->bindParam(":username", $user);
	   $statement->bindParam(":passwd", $passwd);
	   $result = $statement->execute();
	   $row=$statement->fetch();
	   $dbh->commit();
	   $dbh=null;
	   return $row[0];
	}catch (PDOException $e) {
	   print "Error!" . $e->getMessage() . "<br/>";
	   die();
	}
}

// verifies that login infomration matches employee's assigned/first password
function authenticate_emp_assigned($user, $passwd) {
	try {
	   $dbh = connectDB();
	   $dbh->beginTransaction();
	   $statement = $dbh->prepare("SELECT count(*) FROM employee ".
	   "where username = :username and assigned_password = sha2(:passwd,256) ");
	   $statement->bindParam(":username", $user);
	   $statement->bindParam(":passwd", $passwd);
	   $result = $statement->execute();
	   $row=$statement->fetch();
	   $dbh->commit();
	   $dbh=null;
	   return $row[0];
	}catch (PDOException $e) {
	   print "Error!" . $e->getMessage() . "<br/>";
	   die();
	}
}

// verifies that login infomration matches employee's chosed password
function authenticate_emp_new($user, $passwd) {
	try {
	   $dbh = connectDB();
	   $dbh->beginTransaction();
	   $statement = $dbh->prepare("SELECT count(*) FROM employee ".
	   "where username = :username and new_password = sha2(:passwd,256) ");
	   $statement->bindParam(":username", $user);
	   $statement->bindParam(":passwd", $passwd);
	   $result = $statement->execute();
	   $row=$statement->fetch();
	   $dbh->commit();
	   $dbh=null;
	   return $row[0];
	}catch (PDOException $e) {
	   print "Error!" . $e->getMessage() . "<br/>";
	   die();
	}
}

// inserts new customer user into the database
function add_user($userName, $pass, $email, $address, $fstName, $lstName) {
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$id_gen = $dbh->prepare("SELECT Max(id) FROM customer");
		$id_gen->execute();
		$new_cust_id = $id_gen->fetch();
		$new_id = $new_cust_id[0];
		$new_id = $new_id + 1;
		$statement = $dbh->prepare("INSERT INTO customer VALUES".
		"(:id, :usrName, sha2(:passwd,256), :email, :address, :fstName, :lstName)");
		$statement->bindParam(":usrName", $userName);
		$statement->bindParam(":passwd", $pass);
		$statement->bindParam(":email", $email);
		$statement->bindParam(":address", $address);
		$statement->bindParam(":fstName", $fstName);
		$statement->bindParam(":lstName", $lstName);
		$statement->bindParam(":id", $new_id);
		$result = $statement->execute();
		$dbh->commit();
		$dbh=null;
		print "Customer record created successfully!";
		return;
	 }catch (PDOException $e) {
		print "User already exists";
		die();
	 }
}

// returns ID associated with customer's username
function get_cust_ID($username){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT id FROM customer WHERE username = :username");
		$statement->bindParam(":username", $username);
		$result = $statement->execute();
		$id = $statement->fetch();
		$dbh->commit();
		$dbh=null;
		return $id[0];
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// returns ID associated with employee's username
function get_emp_ID($username){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT id FROM employee WHERE username = :username");
		$statement->bindParam(":username", $username);
		$result = $statement->execute();
		$id = $statement->fetch();
		$dbh->commit();
		$dbh=null;
		return $id[0];
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// function adds item and quantity to user's cart 
function add_to_cart($product, $quantity, $usr){
	try {
		// find out if there is a preexisting cart_item corresponding to the product and user
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT count(*) FROM cart_item WHERE product_id = :p AND cust_id = :u");
		$statement->bindParam(":p", $product);
		$statement->bindParam(":u", $usr);
		$result = $statement->execute();
		$row=$statement->fetch();
		// if there is a corresponding item, update its quantity by adding the new quantity
		if ($row[0] == 1){
			$statement = $dbh->prepare("UPDATE cart_item SET quantity = quantity + :q WHERE product_id = :p AND cust_id = :u");
			$statement->bindParam(":u", $usr);
			$statement->bindParam(":q", $quantity);
			$statement->bindParam(":p", $product);
		// if there is not a corresponding item, insert a new cart_item entry 
		} else {
			$statement = $dbh->prepare("INSERT INTO cart_item VALUES (:u, :q, :p)");
			$statement->bindParam(":u", $usr);
			$statement->bindParam(":q", $quantity);
			$statement->bindParam(":p", $product);
		}
		$result = $statement->execute();
		$dbh->commit();
		$dbh=null;
		echo "<p>Product ID " . $product . " successfuly added to cart";
		return;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// function updates customer's password in the datbase 
function update_cust_password($usr, $newPass){
	try {
	$dbh = connectDB();
	$dbh->beginTransaction();
	$statement = $dbh->prepare("UPDATE customer SET password = sha2(:p,256) WHERE id = :u");
	$statement->bindParam(":u", $usr);
	$statement->bindParam(":p", $newPass);
	$result = $statement->execute();
	$dbh->commit();
	$dbh=null;
	echo "<p>Password updated successfully!";
	return;
	} catch (PDOException $e) {
		print "Error! Password may be incorrect length (> 64 characters)" . $e->getMessage() . "<br/>";
		die();
	}
}

// function updates employee's password in the datbase 
function update_emp_password($usr, $newPass){
	try {
	$dbh = connectDB();
	$dbh->beginTransaction();
	//remove the employee's old password 
	$statement = $dbh->prepare("UPDATE employee SET assigned_password = null WHERE id = :u");
	$statement->bindParam(":u", $usr);
	$result = $statement->execute();
	// add the employee's new password 
	$statement = $dbh->prepare("UPDATE employee SET new_password = sha2(:p,256) WHERE id = :u");
	$statement->bindParam(":u", $usr);
	$statement->bindParam(":p", $newPass);
	$result = $statement->execute();
	$dbh->commit();
	$dbh=null;
	echo "<p>Password updated successfully!";
	return;
	} catch (PDOException $e) {
		print "Error! Password may be incorrect length (> 64 characters)" . $e->getMessage() . "<br/>";
		die();
	}
}

// returns the orders placed by the user
function user_orders($usr){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT * FROM cust_order WHERE id = :u");
		$statement->bindParam(":u", $usr);
		$result = $statement->execute();
		$rows = $statement->fetchAll();
		$dbh->commit();
		$dbh=null;
		return $rows;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// returns the items of an order 
function get_items($order){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT * FROM line_item WHERE order_id = :o");
		$statement->bindParam(":o", $order);
		$result = $statement->execute();
		$rows = $statement->fetchAll();
		$dbh->commit();
		$dbh=null;
		return $rows;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}


// returns the name of the product given its id
function get_prod_name($id){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT name FROM product WHERE product_id = :i");
		$statement->bindParam(":i", $id);
		$result = $statement->execute();
		$rows = $statement->fetch();
		$dbh->commit();
		$dbh=null;
		return $rows[0];
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}
// returns the price of the product given its id
function get_prod_price($id){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT price FROM product WHERE product_id = :i");
		$statement->bindParam(":i", $id);
		$result = $statement->execute();
		$rows = $statement->fetch();
		$dbh->commit();
		$dbh=null;
		return $rows[0];
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// returns the items in the user's cart 
function get_items_cart($id){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT * FROM cart_item WHERE cust_id = :i");
		$statement->bindParam(":i", $id);
		$result = $statement->execute();
		$rows = $statement->fetchAll();
		$dbh->commit();
		$dbh=null;
		return $rows;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// places an order for the items in the user's cart 
function checkout($id){
	try {
		$dbh = connectDB();
		$statement = $dbh->prepare("CALL checkout(:i, @o, @p);");
		$statement->bindParam(":i", $id);
		$result = $statement->execute();
		$dbh->beginTransaction();
		$out_product_state = $dbh->prepare('SELECT @p');
		$out_product_result = $out_product_state->execute();
		$out_product = $out_product_state->fetch();
		$out_order_state = $dbh->prepare('SELECT @o');
		$out_order_result = $out_order_state->execute();
		$out_order = $out_order_state->fetch();
		if ($out_product[0] == -1) {
			print "Order Placed Successfully! Your order number is: " . $out_order[0];
		} else {
			$quantity_stmt =  $dbh->prepare("SELECT current_stock from product WHERE product_id = :p;");
			$quantity_stmt->bindParam(":p", $out_product[0]);
			$quantity_return = $quantity_stmt->execute();
			$quantity = $quantity_stmt->fetch();
			print "There are only " . $quantity[0] . " of product ID " . $out_product[0] . " left in stock. Please update your cart.";
		}
		$dbh->commit();
		$dbh=null;
		return;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// updates the quantity of an item in the user's cart
function update_cart_quantity($usr, $product, $new_quant){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("UPDATE cart_item SET quantity = :q WHERE cust_id = :u AND product_id = :p");
		$statement->bindParam(":u", $usr);
		$statement->bindParam(":q", $new_quant);
		$statement->bindParam(":p", $product);
		$result = $statement->execute();
		$dbh->commit();
		$dbh=null;
		return;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// removes an item from the user's cart
function remove_from_cart($usr, $product){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("DELETE FROM cart_item WHERE cust_id = :u AND product_id = :p");
		$statement->bindParam(":u", $usr);
		$statement->bindParam(":p", $product);
		$result = $statement->execute();
		$dbh->commit();
		$dbh=null;
		return;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

// returns am array of the ids of all products in the database 
function get_product_ids(){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$statement = $dbh->prepare("SELECT product_id FROM product;");
		$result = $statement->execute();
		$ids = $statement->fetchAll();
		$dbh->commit();
		$dbh=null;
		return $ids;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

//change the stock of a product
function new_stock($prod, $stock){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$get_old_stock = $dbh->prepare("SELECT current_stock FROM product WHERE product_id = :p");
		$get_old_stock->bindParam(":p", $prod);
		$old_stock_result = $get_old_stock->execute();
		$old_stock = $get_old_stock->fetch();
		$statement = $dbh->prepare("UPDATE product SET current_stock = :s WHERE product_id = :p");
		$statement->bindParam(":s", $stock);
		$statement->bindParam(":p", $prod);
		$result = $statement->execute();
		$log_change = $dbh->prepare("CALL log_product_update(:e, null, now(), \"UPDATE\", null, null, :s, :o, :p, null, \"EMPLOYEE\");");
		$emp = get_emp_id($_SESSION["username"]);
		$log_change->bindParam(":e", $emp);
		$log_change->bindParam(":s", $stock);
		$log_change->bindParam(":o", $old_stock[0]);
		$log_change->bindParam(":p", $prod);
		$log = $log_change->execute();
		$dbh->commit();
		$dbh=null;
		return;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

//change the price of a product
function new_price($prod, $price){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		$get_old_price = $dbh->prepare("SELECT price FROM product WHERE product_id = :p");
		$get_old_price->bindParam(":p", $prod);
		$old_price_result = $get_old_price->execute();
		$old_price = $get_old_price->fetch();
		$statement = $dbh->prepare("UPDATE product SET price = :p WHERE product_id = :i");
		$statement->bindParam(":p", $price);
		$statement->bindParam(":i", $prod);
		$result = $statement->execute();
		$log_change = $dbh->prepare("CALL log_product_update(:e, null, now(), \"UPDATE\", :n, :o, null, null, :p, null, \"EMPLOYEE\");");
		$emp = get_emp_id($_SESSION["username"]);
		$log_change->bindParam(":e", $emp);
		$log_change->bindParam(":n", $price);
		$log_change->bindParam(":o", $old_price[0]);
		$log_change->bindParam(":p", $prod);
		$log = $log_change->execute();
		$dbh->commit();
		$dbh=null;
		return;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}

//return price or stock history for a product
function get_updates($prod, $p_or_s){
	try {
		$dbh = connectDB();
		$dbh->beginTransaction();
		if($p_or_s == "s"){
			$statement = $dbh->prepare("SELECT timestamp, old_stock, new_stock FROM history WHERE product_id = :p AND isnull(old_price);");
		} else {
			$statement = $dbh->prepare("SELECT timestamp, old_price, new_price FROM history WHERE product_id = :p AND isnull(old_stock);");
		}
		$statement->bindParam(":p", $prod);
		$result = $statement->execute();
		$updates = $statement->fetchAll();
		$dbh->commit();
		$dbh=null;
		return $updates;
	} catch (PDOException $e) {
		print "Error!" . $e->getMessage() . "<br/>";
		die();
	}
}
?>