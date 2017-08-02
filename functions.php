<?php
// Functions to be included in other pages

require "cfg.php";

// Connect to database
$conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
if ($conn->connect_error) {
  die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}

// Function to return the studentId of the current sessionId
function getStudentId($sessId) {

  global $conn;

  $sql = 'SELECT studentId FROM session WHERE sessionId = "'.$sessId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
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

  global $conn;

  $studentId = getStudentId($sessId);

  $sql = 'SELECT userName from users WHERE studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  $row = mysqli_fetch_assoc($result);
  return $row['userName'];

  mysqli_free_result($result);

}

// Wrapper function to echo getUserName
function echoGetUserName($sessId) {

  $return = getUserName($sessId);
  echo $return;

}

// Function to get userName from studentId
function userNameFromStudentId($studentId) {

  global $conn;

  $sql = 'SELECT userName FROM users WHERE studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  $row = mysqli_fetch_assoc($result);

  return $row['userName'];

  mysqli_free_result($reuslt);

}

// Function to return the houseName of the current session
function getUserHouseName($sessId) {

  global $conn;

  $studentId = getStudentId($sessId);

  $sql = 'SELECT h.houseName FROM users u JOIN house h ON u.hId = h.hId WHERE u.studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  $row = mysqli_fetch_assoc($result);
  return $row['houseName'];

  mysqli_free_result($result);

}

// Wrapper function to echo getUserHouseName
function echoGetUserHouseName($sessId) {

  $return = getUserHouseName($sessId);
  echo $return;

}

// Function to get hId of current sessionId
function getUserHId($sessId) {

  global $conn;

  $studentId = getStudentId($sessId);

  $sql = 'SELECT hId from users WHERE studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  $row = mysqli_fetch_assoc($result);
  return $row['hId'];

  mysqli_free_result($result);

}

// Function to get user setting
function getUserSetting($studentId,$setting) {

  global $conn;

  $sql = 'SELECT '.$setting.' FROM userSetting WHERE studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  $row = mysqli_fetch_assoc($result);
  return $row["$setting"];

  mysqli_free_result($result);

}

// Function to get userGroup of current session
function getUserGroup($sessId) {

  global $conn;

  $studentId = getStudentId($sessId);

  $sql = 'SELECT userGroup from users WHERE studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  $row = mysqli_fetch_assoc($result);

  return $row['userGroup'];

  mysqli_free_result($result);

}

// Function to get userGroupName of current session
function getUserGroupName($sessId) {

  global $conn;

  $studentId = getStudentId($sessId);

  $sql = 'SELECT g.userGroupName from users u JOIN userGroup g ON u.userGroup = g.userGroup WHERE u.studentId = "'.$studentId.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  $row = mysqli_fetch_assoc($result);

  return $row['userGroupName'];

  mysqli_free_result($result);

}

// Wrapper function to echo userGroupName
function echoGetUserGroupName($sessId) {

  $return = getUserGroupName($sessId);
  echo $return;

}

// Function to check if the current session have certain permission
function havePermission($sessId,$perm) {

  global $conn;

  $userGroup = getUserGroup($sessId);

  $sql = 'SELECT * FROM userPermission WHERE userGroup = "'.$userGroup.'" AND permission = "'.$perm.'";';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  if (($result->num_rows) > 0) {
    return TRUE;
  } else {
    return FALSE;
  }

}

?>
