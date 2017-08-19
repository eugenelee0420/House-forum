<?php
// Page to change one user's userGroup, require login and sufficient permission

require "cfg.php";
require "functions.php";

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

require "sidenav.php";

// Check if user requested any target user
// If not, redirect to index.php
if (!isset($_GET['studentId'])) {
  // Cannot use header because some html have already been sent
  ?>
  <script type="text/javascript">
    window.location = "index.php";
  </script>
  <?php
  die();
}

// Check if the specified user exists
// Also get userName for later use
$stmt = $conn->prepare('SELECT studentId, userName FROM users WHERE studentId = ?');
$stmt->bind_param("s",$_GET['studentId']);
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->error);
}

$stmt->bind_result($studentId,$userName);
$stmt->fetch();

if ($studentId !== $_GET['studentId']) {
  die('The requested studentId does not exist!');
}

$stmt->free_result();
$stmt->close();

// Check permission
if (!havePermission(session_id(),"AUS")) {
  die('You do not have permission to perform this action!');
}

if ($_POST['submit'] == "submit") {
  // Process data

  // Check if user selected anything
  if (strlen($_POST['userGroup']) < 1) {
    die('Please select a userGroup!');
  }

  // Check if submitted userGroup exist
  $stmt = $conn->prepare('SELECT userGroup FROM userGroup WHERE userGroup = ?');
  $stmt->bind_param("s",$_POST['userGroup']);
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

  // Update database
  $stmt = $conn->prepare('UPDATE users SET userGroup = ? WHERE studentId = ?');
  $stmt->bind_param("ss",$_POST['userGroup'],$_GET['studentId']);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->free_result();
  $stmt->close();

  // Display toast
	?>

	<script>
	Materialize.toast('Changes saved.', 4000);
	</script>

	<?php

}

// Display form

// Get the user's userGroup
$stmt = $conn->prepare('SELECT userGroup FROM users WHERE studentId = ?');
$stmt->bind_param("s",$_GET['studentId']);
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->eror);
}

$stmt->bind_result($currentUserGroup);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

// Get all the user groups
$sql = 'SELECT userGroup, userGroupName FROM userGroup;';
$result = $conn->query($sql);
if (!$result) {
  die('Query failed. '.$conn->error);
}

?>

<div class="row"><div class="col s12">
  <h3>User group change</h3>
</div></div>

<div class="row">
<form class="col s12 m12 l6" action="" method="post">

<div class="row">
  <div class="col s12">
    Changing <b><?php echo $_GET['studentId'].' ('.$userName.')'; ?></b> 's user group
  </div>
</div>


<?php

echo '<div class="row"><div class="input-field col s12">';
echo '<select name="userGroup">';

while ($row = mysqli_fetch_assoc($result)) {

  // Select the current user group
  if ($row['userGroup'] == $currentUserGroup) {

    echo '<option value="'.$row['userGroup'].'" selected>'.$row['userGroupName'].'</option>';

  } else {

    echo '<option value="'.$row['userGroup'].'">'.$row['userGroupName'].'</option>';

  }


}

echo '</select>';
echo '<label>User group</label>';
echo '</div></div>';

?>

<button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Apply
<i class="material-icons right">send</i>

</form>
</div>
