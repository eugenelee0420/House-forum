<?php
// Profile page, display information about the requested user, and links to admin actions (change user group etc.)
// Require login

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
    $('.parallax').parallax();
  });
</script>

<?php

require 'sidenav.php';

// Check if user requested a user to display
// If not, redirect to index.php
if (!isset($_GET['studentId'])) {
    // Cannot use header because some html have already been sent ?>
  <script type="text/javascript">
    window.location = "index.php";
  </script>
  <?php
  die();
}

// Get information of the user
$stmt = $conn->prepare('SELECT u.studentId, u.userName, h.houseName, g.userGroupName FROM users u JOIN house h ON u.hId = h.hId JOIN userGroup g ON u.userGroup = g.userGroup WHERE u.studentId = ?');
$stmt->bind_param('s', $_GET['studentId']);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($studentId, $userName, $houseName, $userGroupName);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

// Check if requested user exist
if ($_GET['studentId'] !== $studentId) {
    die('The requested user does not exist!');
}

// Display the info

?>


<div class="parallax-container hide-on-small-only" style="height: 350px">
  <div class="parallax">
    <img class="responsive-img" src="<?php echoGetUserSetting($studentId, 'bgPic'); ?>">
  </div>
</div>

<div class="section white">
  <div class="row container">
    <!-- s10 col for showing avatar pic on phones -->
    <div class="col s10 hide-on-med-and-up">
      <img class="circle responsive-img" src="<?php echoGetUserSetting($studentId, 'avatarPic'); ?>">
    </div>
    <!-- normal s2/s10 division for other devices -->
    <div class="col s2 hide-on-small-only">
      <img class="circle responsive-img" src="<?php echoGetUserSetting($studentId, 'avatarPic'); ?>">
    </div>
    <div class="col s10">
      <h2><?php echo $userName; ?></h2>
      <p>House: <?php echo $houseName; ?></p>
      <p>User Group: <?php echo $userGroupName; ?></p>
    </div>
  </div>
</div>

<div class="parallax-container hide-on-med-and-down" style="height: 350px">
  <div class="parallax">
    <img class="responsive-img" src="<?php echoGetUserSetting($studentId, 'bgPic'); ?>">
  </div>
</div>

<?php

// Check for userGroup editing permission then display FAB to edit userGroup
if (havePermission(session_id(), 'AUS')) {
    echo '<div class="fixed-action-btn">';
    echo '<a href="change_userGroup.php?studentId='.$studentId.'" class="btn-floating btn-large red waves-effect waves-light tooltipped" data-tooltip="Change user group" data-position="left" data-delay="0">';
    echo '<i class="large material-icons">edit</i>';
    echo '</a></div>';
}

?>
