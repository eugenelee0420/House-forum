<?php
// Page the logs user out

require "cfg.php";
require "functions.php";

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
