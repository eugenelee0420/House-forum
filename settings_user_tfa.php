<?php
// User setting page (tfa settings), require login

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

$studentId = getStudentId(session_id());

// Check if user have enabled tfa
$stmt = $conn->prepare('SELECT studentId FROM tfa WHERE studentId = ?');
$stmt->bind_param("s",getStudentId(session_id()));
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->error);
}

$stmt->bind_result($qStudentId);
$stmt->fetch();

if ($qStudentId == $studentId) {

  // tfa have been enabled, verify password to disable

} else {

  // tfa is disabled, show qr code

  $secret = $tfa->createSecret(160,true);
  $data = 'otpauth://totp/House%20Forum?secret='.$secret.'&issuer=House%20Forum';

  echo '<div class="row"><div class="col s12">';
  echo '<h3>Enable 2-factor authentication</h3>';
  echo '</div></div>';

  echo '<div class="row"><div class="col s12">';
  echo '<p>Scan the following QR code with the Google Authenticator app or manually input the secret.</p>';
  echo '<p>Then verify your password and one time token to enable 2-factor authentication.</p>';
  echo '</div></div>';

  echo '<div class="row"><div class="col s10 m4 l2">';
  echo '<img class="materialboxed" width="100%" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&format=svg&data='.$data.'">';
  echo '</div></div>';

  echo '<div class="row"><div class="col s12">';
  echo '<p>Manual input: '.chunk_split($secret, 4, ' ').'</p>';
  echo '</div></div>';

}

?>
