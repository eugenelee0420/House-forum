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
    // Initialize select
    $('select').material_select();
  });
</script>

<?php

require 'sidenav.php';

// Check if user specified any userGroup to delete
// If not, redirect to index.php
if (!isset($_GET['userGroup'])) {
    // Cannot use header because some html have already been sent ?>
  <script type="text/javascript">
    window.location = "index.php";
  </script>
  <?php
  die();
}

// Check if requested userGroup exists
// Get userGroupName for later use
$stmt = $conn->prepare('SELECT userGroup, userGroupName FROM userGroup WHERE userGroup = ?');
$stmt->bind_param('s', $_GET['userGroup']);
$result = $stmt->execute();
if (!$result) {
    die('Query failed. '.$stmt->error);
}

$stmt->bind_result($userGroup, $userGroupName);
$stmt->fetch();

if ($userGroup !== $_GET['userGroup']) {
    die('The requested userGroup does not exist!');
}

$stmt->free_result();
$stmt->close();

// Check permission
if (!havePermission(session_id(), 'AUG')) {
    die('You do not have permission to perform this action!');
}

// Check if form is submitted
if ($_POST['submit'] == 'submit') {

  // Check if user selected anything
    if (strlen($_POST['userGroup']) < 1) {
        die('Please select a userGroup!');
    }

    // Check if submitted userGroup exists
    $stmt = $conn->prepare('SELECT userGroup FROM userGroup WHERE userGroup = ?');
    $stmt->bind_param('s', $_POST['userGroup']);
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->bind_result($qUserGroup);
    $stmt->fetch();

    if ($qUserGroup !== $_POST['userGroup']) {
        die('The selected userGroup does not exist!');
    }

    $stmt->free_result();
    $stmt->close();

    // Move users of userGroup being deleted to selected userGroup
    $stmt = $conn->prepare('UPDATE users SET userGroup = ? WHERE userGroup = ?');
    $stmt->bind_param('ss', $_POST['userGroup'], $_GET['userGroup']);
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->free_result();
    $stmt->close();

    // Delete all associated userPermission entries
    $stmt = $conn->prepare('DELETE FROM userPermission WHERE userGroup = ?');
    $stmt->bind_param('s', $_GET['userGroup']);
    $result = $stmt->execute();
    if (!$result) {
        die('Quer failed. '.$stmt->error);
    }

    $stmt->free_result();
    $stmt->close();

    // Delete the userGroup
    $stmt = $conn->prepare('DELETE FROM userGroup WHERE userGroup = ?');
    $stmt->bind_param('s', $_GET['userGroup']);
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->free_result();
    $stmt->close();

    // Redirect to settings_userGroup.php
    // Cannot use header because some html have already been sent ?>
  <script type="text/javascript">
    window.location = "settings_userGroup.php";
  </script>
  <?php
  die();
} else {

  // Display form

    // Get all userGroup
    $sql = 'SELECT userGroup, userGroupName FROM userGroup';
    $result = $conn->query($sql);
    if (!$result) {
        die('Query failed. '.$conn->error);
    } ?>

  <div class="row"><div class="col s12">
    <h3>Delete user group: <b><?php echo $_GET['userGroup']; ?> (<?php echo $userGroupName; ?>)</b></h3>
  </div></div>

  <div class="row">
    <form class="col s12 m12 l6" action="" method="post">

      <div class="row">
        <div class="input-field col s12">
          <select name="userGroup">

            <?php

            while ($row = mysqli_fetch_assoc($result)) {

              // Display all userGroup, except the one being deleted
                if ($row['userGroup'] !== $_GET['userGroup']) {
                    echo '<option value="'.$row['userGroup'].'">'.$row['userGroupName'].'</option>';
                }
            } ?>

          </select>
          <label>Move all users of the deleted user group to...</label>
        </div>
      </div>

      <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Move and delete
      <i class="material-icons right">send</i>

    </form>
  </div>

  <?php
}
