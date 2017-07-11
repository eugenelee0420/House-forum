<?php
// Page the logs user out

require "cfg.php";

// Connect to database
$conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
if ($conn->connect_error) {
	die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}

session_start();

// Update database (delete the session)
$sql = 'DELETE FROM session WHERE sessionId = "'.session_id().'";';
$result = $conn->query($sql);
// No need to check query (if it failes the user isn't logged in)

$_SESSION['logged_in'] = 0;
$_SESSION['last_activity'] = 0;

session_unset();
session_destroy();

header('Location: login.php');

?>
