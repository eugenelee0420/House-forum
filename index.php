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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/css/materialize.min.css">
<!--Let browser know website is optimized for mobile-->
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body onload="showSideNav();">
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/js/materialize.min.js"></script>

<!-- Initialize mobile sidenav-->
<script>
$(document).ready(function() {
		$(".button-collapse").sideNav();
  });
function showSideNav() {
	$('.button-collapse').sideNav('show');
}
</script>

<nav>
  <div class="nav-wrapper purple lighten-2">
		<div class="row">
			<div class="col s12">
				<!-- Logo -->
    		<a href="index.php" class="brand-logo center flow-text">House Forums</a>

				<!-- Sidenav -->
				<ul id="slide-out" class="side-nav">

					<!-- User-view -->
					<li><div class="user-view">
						<div class="background">
							<img src="https://puu.sh/vutY0.jpg">
						</div>
						<a href="profile.php"><img class="circle" src="https://puu.sh/wFuFj.jpg"></a>
						<span class="white-text name"><?php echoGetUserName(session_id()); ?> (<?php echoGetUserGroupName(session_id()); ?>)</span>
						<span class="white-text email"><?php echoGetStudentId(session_id()); ?></span>
					</div></li>

					<!-- Menu -->
					<?php

					// Show house-specific forum link(s)

					// If the user only have permission to view one house-specific forum (the one they belong to)
					if (havePermission(session_id(),"VH") AND !havePermission(session_id(),"VAH")) {

						// Find the fId of the user's house
						$sql = 'SELECT fId, fName FROM forum WHERE hId = "'.getUserHId(session_id()).'";';
						$result = $conn->query($sql);
						if (!$result) {
							die('Query failed. '.$conn->error);
						}

						$row = mysqli_fetch_assoc($result);

						echo '<li><a href="viewforum.php?fId='.$row['fId'].'" class="waves-effect"><i class="material-icons">chat</i>'.$row['fName'].'</a></li>';

					} elseif (havePermission(session_id(),"VAH")) { // If user have permission to view all houses' forums

						// Find all house forums
						$sql = 'SELECT fId, fName FROM forum WHERE hId IS NOT NULL';
						$result = $conn->query($sql);
						if (!$result) {
							die('Query failed. '.$conn->error);
						}

						// List all the house forums
						while($row = mysqli_fetch_assoc($result)) {

							echo '<li><a href="viewforum.php?fId='.$row['fId'].'" class="waves-effect"><i class="material-icons">chat</i>'.$row['fName'].'</a></li>';

						}

					}

					// Show inter-house forum link
					if (havePermission(session_id(),"VI")) {
						echo '<li><a href="viewforum.php?fId=IHF" class="waves-effect"><i class="material-icons">forum</i>Inter-house Forum</a></li>';
					}

					// Divider
					if (havePermission(session_id(),"AGS") OR havePermission(session_id(),"AUS")) {
						echo '<li><div class="divider"></div></li>';
					}

					// Settings
					// Global settings
					if (havePermission(session_id(),"AGS")) {
						echo '<li><a href="settings_global.php"><i class="material-icons">settings</i>Global Settings</a></li>';
					}

					// userGroup settings
					if (havePermission(session_id(),"AUS")) {
						echo '<li><a href="settings_userGroup.php"><i class="material-icons">settings</i>User Group Settings</a></li>';
					}

					?>

					<li><div class="divider"></div></li>
					<li><a href="settings_user.php" class="waves-effect"><i class="material-icons">settings</i>User Settings</a></li>
					<li><a href="logout.php" class="waves-effect"><i class="material-icons">exit_to_app</i>Logout</a></li>
				</ul>
				<a href="#" data-activates="slide-out" class="button-collapse show-on-large"><i class="material-icons">menu</i></a>

			</div>
		</div>
  </div>
</nav>
