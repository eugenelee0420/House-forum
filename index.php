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
  die('Query failed');
}

echo "Session ID: ".session_id()."<br>";


$sql = 'SELECT studentId FROM session WHERE sessionId = "'.session_id().'";';
$result = $conn->query($sql);
if (!$result) {
  die('Query failed');
}

$row = mysqli_fetch_assoc($result);

echo "Student ID: ".$row['studentId']."<br>";

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
    		<a href="index.php" class="brand-logo center flow-text">House Forums</a>
				<ul id="slide-out" class="side-nav">
					<li><div class="user-view">
						<div class="background">
							<img src="https://puu.sh/vutY0.jpg">
						</div>
						<a href="profile.php"><img class="circle" src="https://puu.sh/wFuFj.jpg"></a>
						<span class="white-text name"><?php getUserName(session_id()); ?></span>
						<span class="white-text email"><?php getStudentId(session_id()); ?></span>
					</div></li>
					<li><a href="#!"><i class="material-icons">cloud</i>First Link With Icon</a></li>
					<li><a href="#!">Second Link</a></li>
					<li><div class="divider"></div></li>
					<li><a class="subheader">Subheader</a></li>
					<li><a class="waves-effect" href="#!">Third Link With Waves</a></li>
				</ul>
				<a href="#" data-activates="slide-out" class="button-collapse show-on-large"><i class="material-icons">menu</i></a>
			</div>
		</div>
  </div>
</nav>
