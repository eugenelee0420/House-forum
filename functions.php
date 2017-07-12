<?php
// Functions to be included in other pages

// Function to return the studentId of the current sessionId
function getStudentId($sessId) {

  require "cfg.php";

  // Connect to database
  $conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
  if ($conn->connect_error) {
  	die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
  }

  $sql = 'SELECT studentId FROM session WHERE sessionId = "'.$sessId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed');
  }

  $row = mysqli_fetch_assoc($result);
  return $row['studentId'];

  mysqli_free_result($result);
}

// Warpper function to echo getStudentId
function echoGetStudentId($sessId) {

  $return = getStudentId($sessId);
  echo $return;

}

// Function to return the userName of the current sessionId
function getUserName($sessId) {

  require "cfg.php";

  // Connect to database
  $conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
  if ($conn->connect_error) {
    die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
  }

  $studentId = getStudentId($sessId);

  $sql = 'SELECT userName from users WHERE studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed');
  }

  $row = mysqli_fetch_assoc($result);
  return $row['userName'];

}

// Wrapper function to echo getUserName
function echoGetUserName($sessId) {

  $return = getUserName($sessId);
  echo $return;

}


?>
