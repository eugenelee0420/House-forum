<?php
// User group settings page, require login and sufficient permission

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

// Check permission
if (!havePermission(session_id(), 'AUG')) {
    die('You do not have permission to perform this action!');
}

// Get the user groups
$sql = 'SELECT userGroup, userGroupName FROM userGroup';
$result = $conn->query($sql);
if (!$result) {
    die('Query failed. '.$conn->error);
}

?>

<div class="row"><div class="col s12">
<h3>User group settings</h3>
</div></div>

<div class="row"><div class="col s12">
<table>

<thead><tr>
<th>User group ID</th>
<th>User group name</th>
<th>Actions</th>
</tr></thead>

<tbody>

<?php

while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';

    echo '<td>'.$row['userGroup'].'</td>';
    echo '<td>'.$row['userGroupName'].'</td>';

    echo '<td>';
    echo '<a class="btn waves-effect waves-light yellow darken-1" href="settings_userGroup_edit.php?userGroup='.$row['userGroup'].'"><i class="material-icons">edit</i>Edit</a>';
    echo ' ';
    echo '<a class="btn waves-effect waves-light red" href="settings_userGroup_delete.php?userGroup='.$row['userGroup'].'"><i class="material-icons">delete</i>Delete</a>';
    echo '</td>';

    echo '</tr>';
}

mysqli_free_result($result);

?>

</tbody>
</table>
</div></div>

<div class="fixed-action-btn">
<a href="settings_userGroup_add.php" class="btn-floating btn-large red waves-effect waves-light tooltipped" data-tooltip="New user group" data-position="left" data-delay="0">
<i class="large material-icons">add</i>
</a></div>
