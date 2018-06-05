<?php
// Page to post new thread (requires login and sufficient permission)
// Self-submitting form

require 'functions.php';

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

require 'sidenav.php';

// Check if user requested a forum to post in
// If not, redirect to index.php
if (!isset($_GET['fId'])) {
    // Cannot use header because some html have already been sent ?>
	<script type="text/javascript">
		window.location = "index.php";
  </script>
	<?php
    die();
}

// Check if requested fId exists
$stmt = $conn->prepare('SELECT fId FROM forum WHERE fId = ?');
$stmt->bind_param('s', $_GET['fId']);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($fId);
$stmt->fetch();

if ($fId !== $_GET['fId']) {
    die('The requested forum does not exist!');
}

$stmt->free_result();
$stmt->close();

// Check forum type then permission
// Also get fName for later use
$stmt = $conn->prepare('SELECT hId, fName FROM forum WHERE fId = ?');
$stmt->bind_param('s', $_GET['fId']);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($hId, $fName);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

if ($hId == null) {

    // Check for PI permission
    if (!havePermission(session_id(), 'PI')) {
        die('You do not have permission to perform this action!');
    }
} else {

      // Check for PH or PAH permission
    if (!havePermission(session_id(), 'PH') and !havePermission(session_id(), 'PAH')) {
        die('You do not have permission to perform this action!');
    }

    // If user only have VH permission
    if (havePermission(session_id(), 'PH') and !havePermission(session_id(), 'PAH')) {

        // Check if the user's house and forum's house match
        if (getUserHId(session_id()) !== $hId) {
            die('You do not have permission to perform this action!');
        }
    }
}

// The user have sufficient permissions, can safely perform actions

if ($_POST['submit'] == 'submit') {

    // Check if all the fields are filled in
    if ((strlen($_POST['tTitle']) < 1) or (strlen($_POST['tContent']) < 1)) {
        die('Please fill in all the fields!');
    }

    // Check field constraint
    if (strlen($_POST['tTitle']) > 40) {
        die('Please do not enter more than 40 characters for the title!');
    }
    if (strlen($_POST['tContent']) > 65535) {
        die('Please do not enter more than 65,535 characters for the content!');
    }

    // Strip html tags
    $title = strip_tags($_POST['tTitle']);
    $content = strip_tags($_POST['tContent']);

    // Get tId
    $sql = 'SELECT MAX(tId) AS tMax FROM thread';
    $result = $conn->query($sql);
    if (!$result) {
        die('Query failed. '.$conn->error);
    }

    $row = mysqli_fetch_assoc($result);
    $tId = (intval($row['tMax']) + 1);

    mysqli_free_result($result);

    // Add to database
    $stmt = $conn->prepare('INSERT INTO thread (tId, tTitle, tContent, tTime, fId, studentId) VALUES ('.$tId.', ?, ?, "'.time().'", ?, ?)');
    $stmt->bind_param('ssss', $title, $content, $_GET['fId'], getStudentId(session_id()));
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->free_result();
    $stmt->close();

    // Redirect to the newly posted thread
    // Cannot use header because some html have already been sent ?>
	<script type="text/javascript">
		window.location = "viewthread.php?tId=<?php echo $tId; ?>";
  </script>
	<?php
    die();
} else {

    // Form not submitted, display form ?>

	<div class="row">
	<form class="col s12 m12 l6" action="" method="post">
		<div class="row">
			<div class="input-field col s12">
				<input id="tTitle" name="tTitle" type="text" data-length="40">
				<label for="tTitle">Title</label>
			</div>
		</div>
		<div class="row">
			<div class="input-field col s12">
				<textarea id="tContent" name="tContent" class="materialize-textarea" data-length="65535"></textarea>
				<label for="tContent">Thread content</label>
			</div>
		</div>
		<p>Formatting with Markdown is supported. <a href="http://commonmark.org/help/" target="_blank">(help)</a></p>
		<p>Posting in:<b><a href="viewforum.php?fId=<?php echo $_GET['fId']; ?>"> <?php echo $fName; ?></a></b></p>
		<button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Post
		<i class="material-icons right">send</i>
	</form>
	</div>
	</div>

	<?php
}

?>
