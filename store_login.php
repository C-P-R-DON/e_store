
<html>
	<body>
		<p><b>Please log in below: </b></p>
		<form method="post" action="store_login.php">
			<label for="Username">Username:</label><br>
			<input type = 'text' name = 'username'><br>
			<label for="Password">Password:</label><br>
			<input type = 'password' name = 'password'><br>
			<?php
			//user clicked the register buttom
			if ( isset($_POST["register"]) ) {
				echo "<label for=\"Password Again\">Password Again:</label><br>";
				echo "<input type = \"password\" name = \"passwordAgain\"><br>";
				echo "<label for=\"First Name\">First Name:</label><br>";
				echo "<input type = 'text' name = 'firstName'><br>";
				echo "<label for=\"Last Name\">Last Name:</label><br>";
				echo "<input type = 'lastName' name = 'lastName'><br>";
				echo "<label for=\"Email\">Email:</label><br>";
				echo "<input type = 'email' name = 'email'><br>";
				echo "<label for='address'>Shipping Address:</label><br>";
				echo "<textarea id='address' name='address' rows='4' cols='50'>";
				echo "</textarea><br>";
				echo "<input type=\"submit\" value=\"Register\" name = \"newRegister\">";
			// user did not click the register button
			} else {
				echo "<input type=\"submit\" value=\"Login\" name = \"login\">";
				echo "<input type=\"submit\" value=\"Register\" name = \"register\">";
			}
			?>
		</form>
	</body>
</html>

<?php
session_start();
require "store_db.php";	
// user clicked the login button */
if ( isset($_POST["login"]) ) {
//check the username and passwd, if correct, redirect to customer_main.php page
	if (authenticate_cust($_POST["username"], $_POST["password"]) ==1) {
		$_SESSION["username"]=$_POST["username"];
		header("LOCATION:customer_main.php");
		return;
	} else if (authenticate_emp_new($_POST["username"], $_POST["password"]) ==1) {
		$_SESSION["username"]=$_POST["username"];
		header("LOCATION:employee_main.php");
		return;
	// if the above step doesn't work, it is the employee's first login and you must change their password
	} else if (authenticate_emp_assigned($_POST["username"], $_POST["password"]) ==1) {
		$_SESSION["username"]=$_POST["username"];
		echo "<p>You must reset your password upon your first login:</p>";
		echo "<form method=\"post\" action=\"store_login.php\">";
			echo "<label for=\"Enter new password\">Enter new password:</label><br>";
			echo "<input type = \"password\" name = \"passwordNew\"><br>";
			echo "<label for=\"Enter new password again\">Enter new password again:</label><br>";
			echo "<input type = \"password\" name = \"passwordNewAgain\"><br>";
			echo "<input type=\"submit\" value=\"Update Password\" name = \"updatePassword\">";
		echo "</form>";
		return;
	} else {
		echo '<p style="color:red">Invalid username or password.</p>';
	}
}

// update password if employee entered new password
if (isset($_POST["updatePassword"])) {
	include_once 'store_db.php';
	// do not allow user to change password if the inputs are null or not matching
	if ($_POST["passwordNew"] != $_POST["passwordNewAgain"] || is_null($_POST["passwordNew"]) || is_null($_POST["passwordNewAgain"])){
		echo "<p>New password does not match or is empty</p>";
	} else {
		$id = get_emp_ID($_SESSION["username"]);
		update_emp_password($id, $_POST["passwordNewAgain"]);
	}
	header("LOCATION:employee_main.php");
}

// add new user to the database once they have registered
if ( isset($_POST["newRegister"], $_POST["username"], $_POST["password"], $_POST["passwordAgain"], $_POST["email"], $_POST["address"], $_POST["firstName"], $_POST["lastName"]) ) {
	// if the user did not enter the same password twice, prompt them to try again
	if ($_POST["password"] != $_POST["passwordAgain"]){
		echo "<p>Password does not match</p>";
	} else {
		include_once 'store_db.php';
		add_user($_POST["username"], $_POST["password"], $_POST["email"], $_POST["address"], $_POST["firstName"], $_POST["lastName"]);
	}
}
?>






