<?php
// User setting page, require login

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

// Get studentId
$studentId = getStudentId(session_id());

$stmt = $conn->prepare('SELECT time, ip FROM loginRecord WHERE studentId = ? ORDER BY time DESC LIMIT 10;');
$stmt->bind_param('s', $studentId);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($time, $ip);

?>

<div class="row"><div class="col s12">
  <h3>Login record</h3>
</div></div>

<div class="row"><div class="col s12">
  <table>

    <thead>
      <tr>
        <th>Time</th>
        <th>IP Address</th>
        <th>Country</th>
      </tr>
    </thead>

    <tbody>



<?php

while ($stmt->fetch()) {
    echo '<tr>';

    echo '<td>';
    echo gmdate('j/n/Y G:i', $time + $timezoneOffset);
    echo '</td>';

    echo '<td>';
    echo $ip;
    echo '</td>';

    echo '<td>';
    echo getCountry($ip);
    echo '</td>';

    echo '</tr>';
}

$stmt->free_result();
$stmt->close();

?>

</tbody></table></div></div>
