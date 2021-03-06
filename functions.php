<?php

// Functions to be included in other pages

$cfgJson = file_get_contents('cfg.json');
$cfg = json_decode($cfgJson, true);

require 'vendor/autoload.php';

$parsedown = new Parsedown();
$tfa = new RobThree\Auth\TwoFactorAuth();

// Connect to database
$conn = new mysqli($cfg['dbHost'], $cfg['dbUser'], $cfg['dbPass'], $cfg['dbName']);
if ($conn->connect_error) {
    die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}

// Function to return the studentId of the current sessionId
function getStudentId($sessId)
{
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
function echoGetStudentId($sessId)
{
    $return = getStudentId($sessId);
    echo $return;
}

// Function to return the userName of the current sessionId
function getUserName($sessId)
{
    global $conn;

    $studentId = getStudentId($sessId);

    $stmt = $conn->prepare('SELECT userName from users WHERE studentId = ?');
    $stmt->bind_param('s', $studentId);
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
function echoGetUserName($sessId)
{
    $return = getUserName($sessId);
    echo $return;
}

// Function to get userName from studentId
function userNameFromStudentId($studentId)
{
    global $conn;

    $stmt = $conn->prepare('SELECT userName FROM users WHERE studentId = ?');
    $stmt->bind_param('s', $studentId);
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
function getUserHouseName($sessId)
{
    global $conn;

    $studentId = getStudentId($sessId);

    $stmt = $conn->prepare('SELECT h.houseName FROM users u JOIN house h ON u.hId = h.hId WHERE u.studentId = ?');
    $stmt->bind_param('s', $studentId);
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
function echoGetUserHouseName($sessId)
{
    $return = getUserHouseName($sessId);
    echo $return;
}

// Function to get hId of current sessionId
function getUserHId($sessId)
{
    global $conn;

    $studentId = getStudentId($sessId);

    $stmt = $conn->prepare('SELECT hId from users WHERE studentId = ?');
    $stmt->bind_param('s', $studentId);
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
function getUserSetting($studentId, $setting)
{
    global $conn;

    $stmt = $conn->prepare('SELECT '.$setting.' FROM userSetting WHERE studentId = ?');
    $stmt->bind_param('s', $studentId);
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
function echoGetUserSetting($studentId, $setting)
{
    $return = getUserSetting($studentId, $setting);
    echo $return;
}

// Function to get userGroup of current session
function getUserGroup($sessId)
{
    global $conn;

    $studentId = getStudentId($sessId);

    $stmt = $conn->prepare('SELECT userGroup from users WHERE studentId = ?');
    $stmt->bind_param('s', $studentId);
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
function getUserGroupName($sessId)
{
    global $conn;

    $studentId = getStudentId($sessId);

    $stmt = $conn->prepare('SELECT g.userGroupName from users u JOIN userGroup g ON u.userGroup = g.userGroup WHERE u.studentId = ?');
    $stmt->bind_param('s', $studentId);
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
function echoGetUserGroupName($sessId)
{
    $return = getUserGroupName($sessId);
    echo $return;
}

// Function to check if the current session have certain permission
function havePermission($sessId, $perm)
{
    global $conn;

    $userGroup = getUserGroup($sessId);

    $stmt = $conn->prepare('SELECT * FROM userPermission WHERE userGroup = ? AND permission = ?');
    $stmt->bind_param('ss', $userGroup, $perm);
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->store_result();

    if (($stmt->num_rows) > 0) {
        return true;
    } else {
        return false;
    }

    $stmt->free_result();
    $stmt->close();
}

// Function to check if thread is pinned
function isPinned($tId)
{
    global $conn;

    $stmt = $conn->prepare('SELECT pin FROM thread WHERE tId = ?');
    $stmt->bind_param('s', intval($tId));
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->bind_result($pin);
    $stmt->fetch();

    if (strval($pin) == '1') {
        return true;
    } elseif (strval($pin) == '0') {
        return false;
    } else {
        return 'INVALID';
    }

    $stmt->free_result();
    $stmt->close();
}

// Function to get user's ip address
function getIp()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
        $method = 'HTTP_CLIENT_IP';
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        $method = 'HTTP_X_FORWARDED_FOR';
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
        $method = 'HTTP_X_FORWARDED';
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
        $method = 'HTTP_FORWARDED_FOR';
    } elseif (getenv('HTTP_FORWARDED')) {
        $ipaddress = getenv('HTTP_FORWARDED');
        $method = 'HTTP_FORWARDED';
    } elseif (getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
        $method = 'REMOTE_ADDR';
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

// Function to get country from IP address
function getCountry($ip)
{
    $ipapi = file_get_contents('http://ip-api.com/json/'.$ip);
    $json = json_decode($ipapi, true);
    $country = $json['country'];

    return $country;
}

// Get global settings
// Not a function, act like cfg.php
// Gets settings from the database and put them into variable
$stmt = $conn->prepare('SELECT value FROM globalSetting WHERE setting = ?');

$setting = 'welcomeMsg';
$stmt->bind_param('s', $setting);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($$setting);
$stmt->fetch();

$setting = 'userTimeout';
$stmt->bind_param('s', $setting);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($$setting);
$stmt->fetch();

$setting = 'timezoneOffset';
$stmt->bind_param('s', $setting);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($$setting);
$stmt->fetch();

$stmt->free_result();
$stmt->close();
