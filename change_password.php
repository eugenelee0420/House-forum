<?php
// Password change page, require login

require "cfg.php";
require "functions.php";

session_start();

// Check if user timed out
$sql = 'SELECT lastActivity FROM session WHERE sessionId = "'.session_id().'";';
$result = $conn->query($sql);
// No need to check query result. If query failed, the user is not logged in
$row = mysqli_fetch_assoc($result);
if ((($row['lastActivity'] + $userTimeout) < time())) {
  // Logout the user
	mysqli_free_result($result);
  $sql = 'DELETE FROM session WHERE sessionId = "'.session_id().'";';
  $conn->query($sql);
  // No need to check result here as well
  session_unset();
}

// Check if user is logged in
if ($_SESSION['logged_in'] !== 1) {
	header('Location: login.php');
	die();
}

// Update last activity
mysqli_free_result($result);
$sql = 'UPDATE session SET lastActivity = '.time().' WHERE sessionId = "'.session_id().'"';
$result = $conn->query($sql);
if (!$result) {
  die('Query failed. '.$conn->error);
}

?>

<!DOCTYPE html>
<html>
<head>
<!--Import Google Icon Font-->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!--Import materialize.css-->
<!-- Compiled and minified CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.1/css/materialize.min.css">
<!--Let browser know website is optimized for mobile-->
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.1/js/materialize.min.js"></script>

<!-- Initialize mobile sidenav-->
<script>
$(document).ready(function() {
		$(".button-collapse").sideNav();
  });
</script>

<?php

require "sidenav.php";

if ($_POST['submit'] == "submit") {

	// Check all fields are filled in
	if ((strlen($_POST['currentPassword']) < 1) OR (strlen($_POST['newPassword']) < 1) OR (strlen($_POST['confirmPassword']) < 1)) {
		die('Please fill in all the fields!');
	}

	// Get the studentId
	$studentId = getStudentId(session_id());

	// Get the password and userName
	$stmt = $conn->prepare('SELECT hash, userName FROM users WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($qHash,$qUserName);
	$stmt->fetch();

	$stmt->free_result();
	$stmt->close();

	// Check current password
	if (!password_verify($_POST['currentPassword'],$qHash)) {
		die('The current password is incorrect!');
	}

	// Check if newPassword and confirmPassword match
	if ($_POST['newPassword'] !== $_POST['confirmPassword']) {
		die('The new password and confirm password do not match!');
	}

	// Check if new password is equal the user's userName
	// Do not allow this because of security reasons

	if ($_POST['newPassword'] == $qUserName) {
		die('Please do not use your userName as your password!');
	}

  // Also not allow password equal to studentId
	if ($_POST['newPassword'] == $studentId) {
		die('Please do not use your studentId as your password!');
	}

	// Update database
	$stmt = $conn->prepare('UPDATE users SET hash = ? WHERE studentId = ?');
	$stmt->bind_param("ss",password_hash($_POST['newPassword'], PASSWORD_DEFAULT),$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	// Display toast
	?>

	<script>
	Materialize.toast('Changes saved.', 4000);
	</script>

	<?php

}

// Display form

?>

<div class="row"><div class="col s12">
	<h3>Password change</h3>
</div></div>

<div class="row">
	<form class="col s12 m12 l6" action="" method="post">

		<div class="row">
			<div class="input-field col s12">
				<input type="password" name="currentPassword" id="currentPassword">
				<label for="currentPassword">Current password</label>
			</div>
		</div>

		<div class="row">
			<div class="input-field col s12">
				<input type="password" name="newPassword" id="newPassword">
				<label for="newPassword">New password</label>
			</div>
		</div>

		<div class="row">
			<div class="input-field col s12">
				<input type="password" name="confirmPassword" id="confirmPassword">
				<label for="confirmPassword">Confirm password</label>
			</div>
		</div>

		<button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Apply
	  <i class="material-icons right">send</i>

	</form>
</div>
