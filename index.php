<?php
// Index page, require login

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
function showSideNav() {
	$('.button-collapse').sideNav('show');
}
</script>

<?php

require "sidenav.php";

// Get the welcome message
$sql = 'SELECT value FROM globalSetting WHERE setting = "welcomeMsg"';
$result = $conn->query($sql);
if (!$result) {
	die('Query failed. '.$conn->error);
}

$row = mysqli_fetch_assoc($result);
$welcomeMsg = $row['value'];

$mdWelcomeMsg = $parsedown->text($welcomeMsg);

echo '<div class="row">';
echo '<div class="col s12 flow-text">';
echo $mdWelcomeMsg;
echo '</div></div>';

?>
