<?php
// login page

require "cfg.php";

// Connect to database
$conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
if ($conn->connect_error) {
	die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}

// Check if form is submitted
if ($_POST['submit'] == "submit") {

  // Login Check
  $stmt=$conn->prepare('SELECT userName, hash ')

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
    		<a href="index.php" class="brand-logo hide-on-small-only flow-text">House Forums</a>
        <a href="index.php" class="brand-logo right hide-on-med-and-up flow-text">House Forums</a>
    		<a href="#" data-activates="mobile-demo" class="button-collapse"><i class="material-icons">menu</i></a>
    		<ul class="right hide-on-med-and-down">
      		<li><a href="profile.php">Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
    		</ul>
    		<ul class="side-nav" id="mobile-demo">
      		<li><a href="profile.php">Profile</a></li>
          <li><a href="logout.php">Logout</a></li>
    		</ul>
			</div>
		</div>
  </div>
</nav>

<!-- Login form -->
<div class="row">
  <form class="col s12 m12 l6" method="post" action="">
    <div class="row">
      <div class="input-field col s12">
        <input id="studentId" name="studentId" type="text">
        <label for="studentId">Student ID</label>
      </div>
    </div>
    <div class="row">
      <div class="input-field col s12">
        <input id="pass" name="pass" type="password">
        <label for="pass">Password</label>
      </div>
    </div>
    <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Submit
    <i class="material-icons right">send</i>
    </button>
  </form>
</div>


</body>
</html>
