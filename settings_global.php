<?php
// User setting page, require login

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
    // Trigger autoresize for the forms
    $('#welcomeMsg').trigger('autoresize');
  });
</script>

<?php

require "sidenav.php";

// Check permission
if (!havePermission(session_id(),"AGS")) {
  die('You do not have permission to access global settings!');
}

if ($_POST['submit'] == "submit") {

  // Form submitted, process data

  // Check all the fields are filled in
  if (strlen($_POST['welcomeMsg']) < 1) {
    die('Please fill in all the fields!');
  }

  // Check field constraint
  if (strlen($_POST['welcomeMsg']) > 65535) {
    die('Please do not enter more than 65,535 characters for the welcome message!');
  }

  // Update database
  // Prepared statement to update setting one by one when there are multiple settings
  $stmt = $conn->prepare('UPDATE globalSetting SET value = ? WHERE setting = ?');

  // Bind param
  $setting = "welcomeMsg";
  $stmt->bind_param("ss",$_POST['welcomeMsg'],$setting);
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
  <h3>Global settings</h3>
</div></div>

<div class="row">
  <form class="col s12 m12 l6" action="" method="post">

    <div class="row">
      <div class="col s12">
        Welcome message on landing page (index.php):
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12">
        <textarea id="welcomeMsg" name="welcomeMsg" class="materialize-textarea"><?php echo getGlobalSetting('welcomeMsg'); ?></textarea>
        <label for="welcomeMsg">Welcome message</label>
      </div>
    </div>

  <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Apply
  <i class="material-icons right">send</i>

  </form>
</div>
