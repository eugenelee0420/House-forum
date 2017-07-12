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
  echo $row['studentId'];

  mysqli_free_result($result);
}

// Function to return the userName of the current sessionId
function getUserName($sessId) {

    // Same as getStudentId
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
    $studentId = $row['studentId'];

    mysqli_free_result($result);

    $sql = 'SELECT userName from users WHERE studentId = "'.$studentId.'";';
    $result = $conn->query($sql);
    if (!$result) {
      die('Query failed');
    }

    $row = mysqli_fetch_assoc($result);
    echo $row['userName'];

    mysqli_free_result($result);

}


?>
