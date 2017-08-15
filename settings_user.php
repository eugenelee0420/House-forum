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
  });
</script>

<?php

require "sidenav.php";

// Get the studentId
$studentId = getStudentId(session_id());

if ($_POST['submit'] == "submit") {

  // Form submitted, process data

  // Check all the fields are filled in
  if ((strlen($_POST['rowsPerPage']) < 1) OR (strlen($_POST['avatarPic']) < 1) OR (strlen($_POST['bgPic']) < 1) OR (strlen($_POST['userName']) < 1)) {
    die('Please fill in all the fields!');
  }

	// Check for invalid value
	if (intval($_POST['rowsPerPage']) < 1) {
		die('Please input an integer larger than 0 for rowsPerPage!');
	}

  // Check field constraint
	if (strlen($_POST['userName']) > 30) {
		die('Please do not enter more than 30 characters for the userName!');
	}

  if (strlen($_POST['avatarPic']) > 200) {
    die('Please do not enter more than 200 characters for avatar image link!');
  }

  if (strlen($_POST['bgPic']) > 200) {
    die('Please do not enter more than 200 characters for background image link!');
  }

	// Check if userName is used
	$stmt = $conn->prepare('SELECT studentId, userName FROM users WHERE userName = ?');
	$stmt->bind_param("s",$_POST['userName']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($qStudentId,$qUserName);
	$stmt->fetch();

	// If the query result returned the inputted userName, and the studentId did not match
	// So that if the user's current userName is entered, error will not be triggered
	if (($qUserName == $_POST['userName']) AND ($qStudentId !== $studentId)) {
		die('The username '.$_POST['userName'].' has been used! Please choose another one.');
	}

	$stmt->free_result();
	$stmt->close();

	// Check if entered userName equal the user's password
	// Do not allow this because of security reasons

	// Get the user's password
	$stmt = $conn->prepare('SELECT hash FROM users WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($qHash);
	$stmt->fetch();

	if (password_verify($_POST['userName'],$qHash)) {
		die('Please do not use your password as your username!');
	}

	$stmt->free_result();
	$stmt->close();

	// Check image
	// Avatar
	$avatarInfo = getimagesize($_POST['avatarPic']);

	// If width or height < 1
	if (($avatarInfo[0] < 1) OR ($avatarInfo[1] < 1)) {
		die('Please input a valid image link for the avatar image!');
	}

	// Check profile ratio
	if ($avatarInfo[0] !== $avatarInfo[1]) {
		die('Please use an image with 1:1 aspect ratio for the avatar image!');
	}

	// Background image
	$bgInfo = getimagesize($_POST['bgPic']);

	// If width or height < 1
	if(($bgInfo[0] < 1) OR ($bgInfo[1] < 1)) {
		die('PLease input a valid image link for the background image!');
	}

  // Update database
  $stmt = $conn->prepare('UPDATE userSetting SET rowsPerPage = ?, avatarPic = ?, bgPic = ? WHERE studentId = ?');
  $stmt->bind_param("isss",floor(intval($_POST['rowsPerPage'])),$_POST['avatarPic'],$_POST['bgPic'],$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->free_result();
  $stmt->close();

	// Update username
	$stmt = $conn->prepare('UPDATE users SET userName = ? WHERE studentId = ?');
	$stmt->bind_param("ss",$_POST['userName'],$studentId);
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

// Get the settings
$stmt = $conn->prepare('SELECT rowsPerPage, avatarPic, bgPic FROM userSetting WHERE studentId = ?');
$stmt->bind_param("s",$studentId);
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->error);
}

$stmt->bind_result($rowsPerPage,$avatarPic,$bgPic);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

?>

<div class="row"><div class="col s12">
  <h3>User settings</h3>
</div></div>

<div class="row">
  <form class="col s12 m12 l6" action="" method="post">

		<div class="row">
			<div class="col s12">
				Username (must be unique):
			</div>
		</div>

		<div class="row">
			<div class="input-field col s12">
				<input id="userName" name="userName" type="text" data-length="30" value="<?php echoGetUserName(session_id()); ?>">
				<label for="userName">Username</label>
			</div>
		</div>

		<div class="row">
			<div class="col s12">
				Change password: Click here --> <a class="btn waves-effect waves-light purple" href="password_change.php">Change password</a>
			</div>
		</div>

    <div class="row">
      <div class="col s12">
        Number of threads displayed per page:
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12">
        <input id="rowsPerPage" name="rowsPerPage" type="number" value="<?php echo $rowsPerPage; ?>">
        <label for="rowsPerPage">Rows per page</label>
      </div>
    </div>

    <div class="row">
      <div class="col s12">
        Avatar image (link to external image)(must be in 1:1 aspect ratio):
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12">
        <input id="avatarPic" name="avatarPic" type="text" data-length="200" value="<?php echo $avatarPic; ?>">
        <label for="avatarPic">Avatar picture</label>
      </div>
    </div>

  <div class="row">
    <div class="col s12">
      Background image (link to external image):
    </div>
  </div>

  <div class="row">
    <div class="input-field col s12">
      <input id="bgPic" name="bgPic" type="text" data-length="200" value="<?php echo $bgPic; ?>">
      <label for="bgPic">Background picture</label>
    </div>
  </div>

  <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Apply
  <i class="material-icons right">send</i>

  </form>
</div>
