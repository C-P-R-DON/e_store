<html>
	<style>
	table, th, td {
	border: 1px solid black;
	border-collapse: collapse;
	}
	</style>
	<body>
		<?php
		session_start();
		// welcome logged in customer
		if (isset($_SESSION["username"])) {
			echo '<p align="left"> <b> Welcome '. $_SESSION["username"].'!</b></p>';
		}
		?>
		<form action = "customer_main.php" method = "post"> 
			<?php
			// if the user is not logged in, display limited input options 
			if (!isset($_SESSION["username"])) {
				echo "<input type=\"submit\" value=\"Login\" name = \"login\"><br><br>";
			// if the user is logged in, display additional input options
			} else {
				echo "<input type=\"submit\" value=\"View Orders\" name = \"viewOrders\">  ";
				echo "<input type=\"submit\" value=\"Shopping Cart\" name = \"shoppingCart\">  ";
				echo "<input type=\"submit\" value=\"Change Password\" name = \"changePassword\">  ";
				echo "<input type=\"submit\" value=\"Logout\" name = \"logout\">";
			}
			?>
			<!-- create selection input for all the available product categories in the database -->
			<select id = "categories" name = "categories">
				<?php 
				include 'store_db.php';
				$categories = get_categories();
				foreach ($categories as $cat) {
					echo '<option value="' . $cat[0] . '">' . $cat[0] . '</option>';
				}
				?>
			</select>
			<input type="submit" value="Search" name = "search">
		</form>
		<?php
		// once the search button is clicked, retrieve the products for each category and display their data 
		if (isset($_POST["search"])) {
			$items = get_products($_POST["categories"]);
			foreach ($items as $row) {
				echo "<b>" . $row[2] . "</b><br>";
				echo "<img src=" . $row[7] . ">";
				echo "Price: $" . $row[4] . "<br>";
				// if the user is logged in, give them addtional options to add quantity of items to their cart 
				if (isset($_SESSION["username"])) {
					echo "<form action = \"customer_main.php\" method = \"post\">"; 
					echo "<input type=\"number\" name=\"quantity\" value = \"1\" min=\"1\" max=\"100\" step=\"1\"><br>";
					echo "<input type='hidden' name='product_id' value='" . $row[0] . "'>";
					echo "<input type=\"submit\" value=\"Add to cart\" name = \"addToCart\">";
					echo "</form>";
				}
			}
		}
		?>
	</body>
</html>

 

<?php
// redirects to login page on pressing login button
if (isset($_POST["login"])) {
	header("LOCATION:store_login.php");
}

// change password form if user clicked on change password
if (isset($_POST["changePassword"])) {
	echo "<form action = \"customer_main.php\" method = \"post\">";
	echo "<label for=\"Old Password\">Old Password:</label><br>";
	echo "<input type = \"password\" name = \"oldPassword\"><br>";
	echo "<label for=\"New Password\">New Password:</label><br>";
	echo "<input type = \"password\" name = \"newPassword\"><br>";
	echo "<label for=\"New Password Again\">Repeat New Password:</label><br>";
	echo "<input type = \"password\" name = \"newPasswordAgain\"><br><br>";
	echo "<input type=\"submit\" value=\"Update Password\" name = \"updatePassword\">";
	echo "</form>";
}

// update password if user entered new password
if (isset($_POST["updatePassword"])) {
	include_once 'store_db.php';
	// do not allow user to change password if the inputs are null or not matching
	if ($_POST["newPassword"] != $_POST["newPasswordAgain"] || is_null($_POST["newPasswordAgain"]) || is_null($_POST["newPasswordAgain"])){
		echo "<p>New password does not match or is empty</p>";
	} else if (authenticate_cust($_SESSION['username'], $_POST["oldPassword"]) != 1) {
		echo "<p>Incorrect old password";
	} else {
		$id = get_cust_ID($_SESSION["username"]);
		update_cust_password($id, $_POST["newPasswordAgain"]);
	}
}

// when logout button is clicked, user is prompted for logout confirmation
if (isset($_POST["logout"])) {
	echo "<p>You are currently logged in. Would you like to log out?   ";
	echo "<form action = \"customer_main.php\" method = \"post\">"; 
	echo "<input type=\"submit\" value=\"Confirm Logout\" name = \"confirmLogout\">  ";
	echo "</form>";
}

// reset session after logout confirmation
if (isset($_POST["confirmLogout"])) {
	session_destroy();
	header("LOCATION:customer_main.php");
}

// add items to shopping cart
if (isset($_POST["addToCart"])) {
	include_once 'store_db.php';
	$quantity = $_POST["quantity"];
	$prod_id = $_POST["product_id"];
	$userID = get_cust_ID($_SESSION["username"]);
	add_to_cart($prod_id, $quantity, $userID);
}

// view shopping cart 
if (isset($_POST["shoppingCart"])) {
	show_cart();
}
function show_cart(){	
	include_once 'store_db.php';
	$id = get_cust_ID($_SESSION["username"]);
	echo "<p><b>Your Shopping Cart:</b></p>";
	?>
	<table>
	<tr>
	<th>Product ID</th>
	<th>Product Name</th>
	<th>Unit Price</th>
	<th>Quantity</th>
	</tr>
	<?php
	$cart_items = get_items_cart($id);
	foreach ($cart_items as $item) {
		echo "<tr>";
		echo "<td>" . $item[2] . "</td>";
		echo "<td>" . get_prod_name($item[2]) . "</td>";
		echo "<td> $" . get_prod_price($item[2]) . "</td>";
		echo "<form action = \"customer_main.php\" method = \"post\">"; 
		echo "<td><input type=\"number\" name=\"quantity\" value = \"" . $item[1] . "\" min=\"1\" max=\"100\" step=\"1\"></td>";
		echo "<td><input type='hidden' name='product_id' value='" . $item[2] . "'>";
		echo "<input type=\"submit\" value=\"Update\" name = \"update\">";
		echo "<input type=\"submit\" value=\"Remove\" name = \"remove\">";
		echo "</form></td>";
		echo "</tr>";
	}
	echo "<table><br>";
	echo "<form action = \"customer_main.php\" method = \"post\">"; 
	echo "<input type=\"submit\" value=\"Checkout\" name = \"checkout\">";
	echo "</form>";
}


// shopping cart quantity update 
if (isset($_POST["update"])) {
	include_once 'store_db.php';
	$new_quant = $_POST["quantity"];
	$id = get_cust_ID($_SESSION["username"]);
	$product = $_POST["product_id"];
	update_cart_quantity($id, $product, $new_quant);
	show_cart();
}

// shopping cart item remove 
if (isset($_POST["remove"])) {
	include_once 'store_db.php';
	$id = get_cust_ID($_SESSION["username"]);
	$product = $_POST["product_id"];
	remove_from_cart($id, $product);
	show_cart();
}

// shopping cart checkout (sql procedure)
if (isset($_POST["checkout"])) {
	include_once 'store_db.php';
	$id = get_cust_ID($_SESSION["username"]);
	checkout($id);
}

// view orders
if (isset($_POST["viewOrders"])) {
	include_once 'store_db.php';
	$id = get_cust_ID($_SESSION["username"]);
	echo "<p>Here are your (id: " . $id . ") orders:</p>";
	$order_meta = user_orders($id);
	foreach ($order_meta as $key => $row) {
		echo "<p><b>" . $key + 1 . ".</b>";
		echo " Order ID: " . $row[0] . "<br>";
		echo "  Order Time: " . $row[3] . "<br>";
		echo "  Total Amount: $" . $row[4] . "</p>";
		?>

		<table>
		<tr>
		<th>Product ID</th>
		<th>Product Name</th>
		<th>Unit Price</th>
		<th>Quantity</th>
		</tr>
	   
		<?php
		$line_items = get_items($row[0]);
		foreach ($line_items as $item) {
		echo "<tr>";
		echo "<td>" . $item[1] . "</td>";
		echo "<td>" . get_prod_name($item[1]) . "</td>";
		echo "<td> $" . $item[3] . "</td>";
		echo "<td>" . $item[2] . "</td>";
		echo "</tr>";
		}
		echo "<table><br>";
	}
}
?>
