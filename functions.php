<?php
// Functions to be included in other pages

require "cfg.php";

require "Parsedown.php";
$parsedown = new Parsedown();

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

  $stmt = $conn->prepare('SELECT userName from users WHERE studentId = ?');
  $stmt->bind_param("s",$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($userName);
  $stmt->fetch();

  return $userName;

  $stmt->free_result();
  $stmt->close();

}

// Wrapper function to echo getUserName
function echoGetUserName($sessId) {

  $return = getUserName($sessId);
  echo $return;

}

// Function to get userName from studentId
function userNameFromStudentId($studentId) {

  global $conn;

  $stmt = $conn->prepare('SELECT userName FROM users WHERE studentId = ?');
  $stmt->bind_param("s",$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($userName);
  $stmt->fetch();

  return $userName;

  $stmt->free_result();
  $stmt->close();

}

// Function to return the houseName of the current session
function getUserHouseName($sessId) {

  global $conn;

  $studentId = getStudentId($sessId);

  $stmt = $conn->prepare('SELECT h.houseName FROM users u JOIN house h ON u.hId = h.hId WHERE u.studentId = ?');
  $stmt->bind_param("s",$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($houseName);
  $stmt->fetch();

  return $houseName;

  $stmt->free_result();
  $stmt->close();

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

  $stmt = $conn->prepare('SELECT hId from users WHERE studentId = ?');
  $stmt->bind_param("s",$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($hId);
  $stmt->fetch();

  return $hId;

  $stmt->free_result();
  $stmt->close();

}

// Function to get user setting
function getUserSetting($studentId,$setting) {

  global $conn;

  $stmt = $conn->prepare('SELECT '.$setting.' FROM userSetting WHERE studentId = ?');
  $stmt->bind_param("s",$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($setting);
  $stmt->fetch();

  return $setting;

  $stmt->free_result();
  $stmt->close();

}

// Wrapper function to echo getUserSetting
function echoGetUserSetting($studentId,$setting) {

  $return = getUserSetting($studentId,$setting);
  echo $return;

}

// Function to get userGroup of current session
function getUserGroup($sessId) {

  global $conn;

  $studentId = getStudentId($sessId);

  $stmt = $conn->prepare('SELECT userGroup from users WHERE studentId = ?');
  $stmt->bind_param("s",$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($userGroup);
  $stmt->fetch();

  return $userGroup;

  $stmt->free_result();
  $stmt->close();

}

// Function to get userGroupName of current session
function getUserGroupName($sessId) {

  global $conn;

  $studentId = getStudentId($sessId);

  $stmt = $conn->prepare('SELECT g.userGroupName from users u JOIN userGroup g ON u.userGroup = g.userGroup WHERE u.studentId = ?');
  $stmt->bind_param("s",$studentId);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($userGroupName);
  $stmt->fetch();

  return $userGroupName;

  $stmt->free_result();
  $stmt->close();

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

  $stmt = $conn->prepare('SELECT * FROM userPermission WHERE userGroup = ? AND permission = ?');
  $stmt->bind_param("ss",$userGroup,$perm);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->store_result();

  if (($stmt->num_rows) > 0) {
    return TRUE;
  } else {
    return FALSE;
  }

  $stmt->free_result();
  $stmt->close();

}

// Function to check if thread is pinned
function isPinned($tId) {

  global $conn;

  $stmt = $conn->prepare('SELECT pin FROM thread WHERE tId = ?');
  $stmt->bind_param("s",intval($tId));
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($pin);
  $stmt->fetch();

  if (strval($pin) == "1") {
    return TRUE;
  } elseif (strval($pin) == "0") {
    return FALSE;
  } else {
    return "INVALID";
  }

  $stmt->free_result();
  $stmt->close();

}

?>
