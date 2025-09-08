<style>
table, th, td {
 border: 1px solid black;
 border-collapse: collapse;
}
</style>

<?php
session_start();
if (!isset($_SESSION['username'])) {
	header("LOCATION:login.php");
}
require "db.php";
if (isset($_POST["accounts"])) {
 $accounts = get_accounts($_SESSION["username"]);
?>

 <table>
 <tr>
 <th>Account</th>
 <th>Balance</th>
 </tr>

 <?php
 foreach ($accounts as $row) {
 echo "<tr>";
 echo "<td>" . $row[0] . "</td>";
 echo "<td>" . $row[1] . "</td>";
 echo "</tr>";
 }
 echo "<table>";
}

if (isset($_POST["confirm"])) {
	$from = $_POST["from_account"];
	$to = $_POST["to_account"];
	$amount = $_POST["amount"];
	$user = $_SESSION["username"];
   $result = transfer($from, $to, $amount, $user);
   echo $result;
}

if (isset($_POST["transfer"])) {
?>

<form method="post" action="bankoperation.php">
	<label for="From Account:">From account:</label>
	<input type = 'text' name = 'from_account'><br>
	<label for="To Account:">To account:</label>
	<input type = 'text' name = 'to_account'><br>
	<label for="Amount:">Amount:</label>
	<input type = 'number' name = 'amount'><br>
	<input type="submit" value="Confirm" name = "confirm">
</form>

<?php
}
?>