<?php
// Index page, require login

require "cfg.php";
require "functions.php";

session_start();

// Connect to database
$conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
if ($conn->connect_error) {
	die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}

// Check if user timed out
$sql = 'SELECT lastActivity FROM session WHERE sessionId = "'.session_id().'";';
$result = $conn->query($sql);
// No need to check query result. If query failed, the user is not logged in
$row = mysqli_fetch_assoc($result);
if ((($row['lastActivity'] + $userTimeout) < time())) {
  // Logout the user
  session_unset();
	mysqli_free_result($result);
  $sql = 'DELETE FROM session WHERE sessionId = "'.session_id().'";';
  $conn->query($sql);
  // No need to check result here as well
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
<body>
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/js/materialize.min.js"></script>

<!-- Initialize mobile sidenav-->
<script>
$(document).ready(function() {
		$(".button-collapse").sideNav();
  });
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
						<span class="white-text name"><?php echoGetUserName(session_id()); ?></span>
						<span class="white-text email"><?php echoGetStudentId(session_id()); ?></span>
					</div></li>

					<!-- Menu -->
					<li><a href="index.php" class="waves-effect"><i class="material-icons">chat</i><?php echoGetUserHouseName(session_id()); ?> House Forum</a></li>
					<li><a href="" class="waves-effect"><i class="material-icons">forum</i>Inter-house Forum</a></li>

					<li><div class="divider"></div></li>
					<li><a href="logout.php" class="waves-effect"><i class="material-icons">exit_to_app</i>Logout</a></li>
				</ul>
				<a href="#" data-activates="slide-out" class="button-collapse show-on-large"><i class="material-icons">menu</i></a>

			</div>
		</div>
  </div>
</nav>


<?php

// Get hId of current session
$hId = getUserHId(session_id());

// Find the relative forum of the house the user belongs to
$sql = 'SELECT fId FROM forum WHERE hId = "'.$hId.'"';
$result = $conn->query($sql);
if (!$result) {
	die('Query failed. '.$conn->error);
}

$row = mysqli_fetch_assoc($result);
$fId = $row['fId'];

mysqli_free_result($result);

// Count the number of threads
$sql = 'SELECT COUNT(*) AS numT FROM thread WHERE fId = "'.$fId.'";';
$result = $conn->query($sql);
if (!$result) {
	die('Query failed. '.$conn->error);
}

$row = mysqli_fetch_assoc($result);
$numT = $row['numT'];

// Get user's rowsPerPage
$rowsPerPage = getUserSetting(session_id(),"rowsPerPage");

// Calculate how many pages are needed
$numPage = ceil($numT/$rowsPerPage);

// Get current page
if (!isset($_GET['page']) OR $_GET['page'] < 1) {
	$cPage = 1;
} else {
	$cPage = intval($_GET['page']);
}

// Pagination
echo '<div class="row"><div class="col s12">';
echo '<ul class="pagination">';

// Backwawrds button
// <li class="(disabled)"><a href="#!"><i class="material-icons">chevron_left</i></a></li>
echo '<li class="';
if ($cPage < 2) {
	echo 'disabled';
} else {
	echo 'waves-effect';
}
echo '">';
echo '<a href="index.php?page='.($cPage - 1).'">';
echo '<i class="material-icons">chevron_left</i></a></li>';

// Numbers
// Active: <li class="active"><a href="#!">1</a></li>
// Not active: <li class="waves-effect"><a href="#!">2</a></li>
for ($x=1;$x<=$numPage;$x++) {
	echo '<li class="';
	if ($x == $cPage) {
		echo 'active';
	} else {
		echo 'waves-effect';
	}
	echo '">';
	echo '<a href="index.php?page='.$x.'">'.$x.'</a></li>';
}

// Forward button
// <li class="waves-effect"><a href="#!"><i class="material-icons">chevron_right</i></a></li>
echo '<li class="';
if ($cPage == $numPage) {
	echo 'disabled';
} else {
	echo 'waves-effect';
}
echo '">';
echo '<a href="index.php?page='.($cPage + 1).'">';
echo '<i class="material-icons">chevron_right</i></a></li>';

echo '</ul>';
echo '</div></div>';

?>
