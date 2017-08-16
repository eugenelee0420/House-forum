<?php
// Page to receive post requests and add users
// Used by the school to add many users at a timed
// Usage: comment out die() at the top, then send POST requests to this pages
/*
Parameters:
studentId - student's student Id (the account's username will be the same as studentId, until the student change it)
pass - student's password (should be randomly generated, then students can change it later)
hId - house ID of the student
userGroup - the user group of the student
*/

// Comment out this line before use
die('Please edit this file and comment out the first line to use');

// Check if all required parameters are set
if (!isset($_POST['studentId']) OR !isset($_POST['pass']) OR !isset($_POST['hId']) OR !isset($_POST['userGroup'])) {
	die('Please specify all the required parameters! Refer to this file for more details');
}

require "cfg.php";

// Echo all the input parameters
echo 'studentId: '.$_POST['studentId'].'<br>';
echo 'pass: '.$_POST['pass'].'<br>';
echo 'hId: '.$_POST['hId'].'<br>';
echo 'userGroup: '.$_POST['userGroup'].'<br>';

// Connect to the database
$conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
if ($conn->connect_error) {
	die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}

// Hash the input password
$hash = password_hash($_POST['pass'], PASSWORD_DEFAULT);

echo "<br>";
echo 'Generated hash: '.$hash.'<br>';

$stmt = $conn->prepare('INSERT INTO users VALUES (?, ?, ?, ?, "'.$hash.'");');
$stmt->bind_param("ssss",$_POST['studentId'],$_POST['studentId'],$_POST['hId'],$_POST['userGroup']);
$result = $stmt->execute();

// Check and echo query result
if (!$result) {
	echo "Add user query status: Failed<br>";
	echo "Error: ".$stmt->error;
	$stmt->free_result();
	die();
} else {
	echo "Add user query status: Success<br>";
}

$stmt->free_result();

// Create a userSetting record
$stmt = $conn->prepare('INSERT INTO userSetting (studentId) VALUES (?);');
$stmt->bind_param("s",$_POST['studentId']);
$result = $stmt->execute();

// Check and echo query result
if (!$result) {
	echo "Create setting record query status: Failed<br>";
	echo "Error: ".$stmt->error;
	$stmt->free_result();
	die();
} else {
	echo "Create setting record query status: Success<br>";
}

$stmt -> free_result();

?>
