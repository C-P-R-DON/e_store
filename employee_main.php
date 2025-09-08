<?php
session_start();
// redirect to login page if not yet logged in
if (!isset($_SESSION["username"])){
	header("LOCATION:store_login.php");
}
echo '<p align="left"> <b> Welcome '. $_SESSION["username"].'!</b></p>';
?>

<html>
	<style>
		table, th, td {
		border: 1px solid black;
		border-collapse: collapse;
		}
	</style>
	<body>
	 	<form action = "employee_main.php" method = "post">
			<input type = "submit" value  = "View Product Stock History" name = "stockHistory"><br><br>
			<input type = "submit" value  = "View Product Price History" name = "priceHistory"><br><br>
			<input type = "submit" value  = "Change Product Stock" name = "restock"><br><br>
			<input type = "submit" value  = "Change Product Price" name = "changePrice"><br><br>
			<input type="submit" value="Logout" name = "logout">
	</body> 
</html>


<?php
// if restock product is selected 
if (isset($_POST["restock"])) {
	?>
		<form action = "employee_main.php" method = "post">
			<!-- create selection input for all the available products in the database -->
			<p><b>Choose Product To Restock:</b></p>
			<label for="products">Select Product ID:</label><br>
			<select id = "Products" name = "productsStock">
				<?php 
				include_once 'store_db.php';
				$products = get_product_ids();
				foreach ($products as $product) {
					echo '<option value="' . $product[0] . '">' . $product[0] . '</option>';
				}
				?>
			</select><br>
			<label for="newStock">Enter or select new stock amount:</label><br>
			<input type="number" name="newStock" value = "0" min="0" max="10000" step="1"><br>
			<input type="submit" value="Submit Change" name = "submitChangeStock">
		</form>
		<?php
}

// if change product price is selected
if (isset($_POST["changePrice"])) {
	?>
		<form action = "employee_main.php" method = "post">
			<!-- create selection input for all the available products in the database -->
			<p><b>Choose Product To Reprice:</b></p>
			<label for="products">Select Product ID:</label><br>
			<select id = "Products" name = "productsPrice">
				<?php 
				include_once 'store_db.php';
				$products = get_product_ids();
				foreach ($products as $product) {
					echo '<option value="' . $product[0] . '">' . $product[0] . '</option>';
				}
				?>
			</select><br>
			<label for="newStock">Enter or select new price:</label><br>
			<input type="number" name="newPrice" value = "0" min="0" max="10000" step="0.01"><br>
			<input type="submit" value="Submit Change" name = "submitChangePrice">
		</form>
		<?php
}

// if view stock history option is selected  
if (isset($_POST["stockHistory"])) {
	?>
	<form action = "employee_main.php" method = "post">
		<!-- create selection input for all the available products in the database -->
		<p><b>Choose Product To View Stock History:</b></p>
		<label for="products">Select Product ID:</label><br>
		<select id = "Products" name = "productsStockHist">
			<?php 
			include_once 'store_db.php';
			$products = get_product_ids();
			foreach ($products as $product) {
				echo '<option value="' . $product[0] . '">' . $product[0] . '</option>';
			}
			?>
		</select><br>
		<input type="submit" value="View History" name = "submitStockHistory">
	</form>
	<?php
}

// if view price histry option is selected 
if (isset($_POST["priceHistory"])) {
	?>
	<form action = "employee_main.php" method = "post">
		<!-- create selection input for all the available products in the database -->
		<p><b>Choose Product To View Price History:</b></p>
		<label for="products">Select Product ID:</label><br>
		<select id = "Products" name = "productsPriceHist">
			<?php 
			include_once 'store_db.php';
			$products = get_product_ids();
			foreach ($products as $product) {
				echo '<option value="' . $product[0] . '">' . $product[0] . '</option>';
			}
			?>
		</select><br>
		<input type="submit" value="View History" name = "submitPriceHistory">
	</form>
	<?php
}

// reset session after logout 
if (isset($_POST["logout"])) {
	session_destroy();
	header("LOCATION:store_login.php");
}

// update database when product stock is changed 
if (isset($_POST["submitChangeStock"])) {
	include_once 'store_db.php';
	$product = $_POST["productsStock"];
	$newStock = $_POST["newStock"];
	new_stock($product, $newStock);
	echo "<p>Stock updated successfully";
}

// update database when product price is changed
if (isset($_POST["submitChangePrice"])) {
	include_once 'store_db.php';
	$product = $_POST["productsPrice"];
	$newPrice = $_POST["newPrice"];
	new_price($product, $newPrice);
	echo "<p>Price updated successfully";
}

// display stock update history for given product
if (isset($_POST["submitStockHistory"])) {
	include_once 'store_db.php';
	echo "<p><b>Product " . $_POST["productsStockHist"] . " Stock History:</b></p>";
	?>
	<table>
	<tr>
	<th>Time</th>
	<th>Old Stock</th>
	<th>New Stock</th>
	<th>Change</th>
	</tr>
	<?php
	$updates = get_updates($_POST["productsStockHist"], "s");
	foreach ($updates as $update) {
	echo "<tr>";
	echo "<td>" . $update[0] . "</td>";
	echo "<td>" . $update[1] . "</td>";
	echo "<td>" . $update[2] . "</td>";
	echo "<td>" . $update[2] - $update[1] . "</td>";
	echo "</tr>";
	}
	echo "<table><br>";
}

// display price update history for given product
if (isset($_POST["submitPriceHistory"])) {
	include_once 'store_db.php';
	echo "<p><b>Product " . $_POST["productsPriceHist"] . " Price History:</b></p>";
	?>
	<table>
	<tr>
	<th>Time</th>
	<th>Old Price</th>
	<th>New Price</th>
	<th>Percent Change</th>
	</tr>
	<?php
		$updates = get_updates($_POST["productsPriceHist"], "p");
		foreach ($updates as $update) {
		echo "<tr>";
		echo "<td>" . $update[0] . "</td>";
		echo "<td> $" . $update[1] . "</td>";
		echo "<td> $" . $update[2] . "</td>";
		echo "<td>" . ((($update[2] - $update[1]) / $update[1]) * 100) . "</td>";
		echo "</tr>";
		}
		echo "<table><br>";
}
?>