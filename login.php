<?php
// login page
require "cfg.php";

session_start();

// Connect to database
$conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
if ($conn->connect_error) {
	die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}

// Check if form is submitted
if ($_POST['submit'] == "submit") {

  // Login Check
  $stmt = $conn->prepare('SELECT studentId, hash FROM users WHERE studentId = ? ');
  $stmt->bind_param("s",$_POST['studentId']);
  $result = $stmt->execute();

  // Check query result
  if (!$result) {
    die('Query failed');
  }

  $stmt->bind_result($qStudentId,$qHash);
  $stmt->fetch();
	$stmt->free_result();

  // Check if the inputted studentId is correct / exists
  if ($qStudentId !== $_POST['studentId']) {
    die('Student ID or password is incorrect!');
  }

  // Check password
  if (password_verify($_POST['pass'],$qHash)) {

    // Login success
    // regenerate session id on Login
    session_regenerate_id();

    // Update database (update before setting variable. If update failed (maybe duplicate session id), user need to retry and generate new session id)
    $stmt = $conn->prepare('INSERT INTO session VALUES ("'.session_id().'", ?, '.time().');');
    $stmt->bind_param("s",$qStudentId);
    $result = $stmt->execute();
    if (!$result) {
      die('Query failed! Please retry');
    }
    // Set session variable to indicate logged in
    $_SESSION['logged_in'] = 1;

    // Update last activity
    $_SESSION['last_activity'] = time();

  } else {
    // Login failed
    die('Student ID or password is incorrect!');
  }

// If form was not submitted, display the form (html)

} else {

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

<?php
// Close if statement
}
?>
