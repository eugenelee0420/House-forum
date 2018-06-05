<?php

// Page to receive post requests and add users
// Used by the school to add many users at a time
// Usage: upload a users.json containing the data to be added, then execute this script
/*

Parameters:
studentId - student's student Id (the account's username will be the same as studentId, until the student change it)
pass - student's password (should be randomly generated, then students can change it later)
hId - house ID of the student (refer to 'house' table)
userGroup - the user group of the student (refer to 'userGroup' table)

Sample JSON data:
[
  {
    "studentId":"s000001",
    "pass":"password",
    "hId":"J",
    "userGroup":"ST"
  },
  {
    "studentId":"s000002",
    "pass":"password",
    "hId":"L",
    "userGroup":"TE"
  }
]

*/

require 'functions.php';

if (!file_exists('users.json')) {
    die('Error: users.json does not exist');
}

echo 'Load file and parse JSON...<br>';

$file = file_get_contents('users.json');

$json = json_decode($file, true);

if ($json === null) {
    die('Error: Invalid JSON');
}

echo 'Parsed JSON data: (PHP array) <br>';

echo '<pre>';
var_dump($json);
echo '</pre>';

// Prepare empty array for checking for duplicate value within input data
$studentIdArray = [];

echo 'Validating input JSON...<br>';

foreach ($json as $key => $row) {

    // Append studentId to array
    array_push($studentIdArray, $row['studentId']);

    if (strlen($row['studentId']) < 1) {
        die('Error: studentId parameter is empty or not correctly specified for entry number '.$key);
    }

    if (strlen($row['pass']) < 1) {
        die('Error: pass parameter is empty or not correctly specified (entry '.$key.', studentId '.$row['studentId'].')');
    }

    if (strlen($row['hId']) < 1) {
        die('Error: hId parameter is empty or not correctly specified (entry '.$key.', studentId '.$row['studentId'].')');
    }

    if (strlen($row['userGroup']) < 1) {
        die('Error: userGroup parameter is empty or not correctly specified (entry '.$key.', studentId '.$row['studentId'].')');
    }

    if (strlen($row['studentId']) > 7) {
        die('Error: studentId parameter exceed the database field limit of 7 bytes (entry '.$key.', studentId '.$row['studentId'].')');
    }
}

echo 'No error was found<br><br>';

unset($row);
unset($key);

echo 'Checking for duplicate studentId within input data...<br>';

if (count($studentIdArray) !== count(array_unique($studentIdArray))) {
    die('Error: Duplicate studentId found within input data');
}

echo 'No error was found<br><br>';

echo 'Verifying parameters...<br>';

$stmt_studentId = $conn->prepare('SELECT studentId FROM users WHERE studentId = ?');
$stmt_hId = $conn->prepare('SELECT hId FROM house WHERE hId = ?');
$stmt_userGroup = $conn->prepare('SELECT userGroup FROM userGroup WHERE userGroup = ?');

foreach ($json as $key => $row) {
    $stmt_studentId->bind_param('s', $row['studentId']);
    $stmt_hId->bind_param('s', $row['hId']);
    $stmt_userGroup->bind_param('s', $row['userGroup']);

    $result_studentId = $stmt_studentId->execute();

    if (!$result_studentId) {
        die('Error: Database query failed (entry '.$key.', studentId '.$row['studentId'].'). '.$stmt_studentId->error);
    }

    $stmt_studentId->bind_result($qStudentId);
    $stmt_studentId->fetch();

    if ($qStudentId == $row['studentId']) {
        die('Error: studentId already exist in database (entry '.$key.', studentId '.$row['studentId'].')');
    }

    $stmt_studentId->free_result();

    $result_hId = $stmt_hId->execute();
    if (!$result_hId) {
        die('Error: Database query failed (entry '.$key.', studentId '.$row['studentId'].'). '.$stmt_hId->error);
    }

    $stmt_hId->bind_result($qHId);
    $stmt_hId->fetch();

    if ($qHId !== $row['hId']) {
        die('Error: Specified hId does not exist (entry '.$key.', studentId '.$row['studentId'].')');
    }

    $stmt_hId->free_result();

    $result_userGroup = $stmt_userGroup->execute();
    if (!$result_userGroup) {
        die('Error: Database query failed (entry '.$key.', studentId '.$row['studentId'].'). '.$stmt_userGroup->error);
    }

    $stmt_userGroup->bind_result($qUserGroup);
    $stmt_userGroup->fetch();

    if ($qUserGroup !== $row['userGroup']) {
        die('Error: Specified userGroup does not exist (entry '.$key.', studentId '.$row['studentId'].')');
    }

    $stmt_userGroup->free_result();
}

echo 'No error was found<br><br>';

$stmt_studentId->close();
$stmt_hId->close();
$stmt_userGroup->close();

unset($key);
unset($row);

echo 'Adding users into the database...<br><br>';

$stmt_users = $conn->prepare('INSERT INTO users (studentId, userName, hId, userGroup, hash) VALUES (?,?,?,?,?)');
$stmt_userSetting = $conn->prepare('INSERT INTO userSetting (studentId) VALUES (?)');

foreach ($json as $key => $row) {
    $hash = password_hash($row['pass'], PASSWORD_DEFAULT);

    echo 'Processing entry '.$key.'<br>';
    echo 'studentId: '.$row['studentId'].'<br>';
    echo 'hId: '.$row['hId'].'<br>';
    echo 'userGroup: '.$row['userGroup'].'<br>';
    echo 'pass: '.$row['pass'].'<br>';
    echo 'hashed password: '.$hash.'<br>';

    echo '-----<br>';

    echo 'Inserting row into users table...<br>';

    $stmt_users->bind_param('sssss', $row['studentId'], $row['studentId'], $row['hId'], $row['userGroup'], $hash);
    $stmt_userSetting->bind_param('s', $row['studentId']);

    $result_users = $stmt_users->execute();
    if (!$result_users) {
        // Will not terminate script execution
        echo 'Error: Database query failed (entry '.$key.', studentId '.$row['studentId'].') '.$stmt_users->error.'<br>';
        $error = 1;
    } else {
        echo 'Database query successfully executed<br>';
    }

    $stmt_users->free_result();

    echo 'Inserting row into userSetting table...<br>';

    $result_userSetting = $stmt_userSetting->execute();
    if (!$result_userSetting) {
        // Will not terminate script execution
        echo 'Error: Database query failed (entry '.$key.', studentId '.$row['studentId'].') '.$stmt_userSetting->error.'<br>';
        $error = 1;
    } else {
        echo 'Database query successfully executed<br>';
    }

    $stmt_userSetting->free_result();

    echo '<br>';
}

unset($key);
unset($row);

if ($error == 1) {
    echo 'Script completed with errors. Check the output for more information<br>';
} else {
    echo 'Script completed without error<br>';
}
