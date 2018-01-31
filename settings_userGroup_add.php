<?php
// User group settings page, require login and sufficient permission


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
  });
</script>

<?php

require "sidenav.php";

// Check permission
if (!havePermission(session_id(),"AUG")) {
  die('You do not have permission to perform this action!');
}

// Check if form is submitted
if ($_POST['submit'] == "submit") {

  // Check if all fields are filled in
  if ((strlen($_POST['userGroup']) < 1) OR (strlen($_POST['userGroupName']) < 1) OR (strlen($_POST['userGroupDescription']) < 1)) {
    die('Please fill in all the fields!');
  }

  // Check field constraint
  if (strlen($_POST['userGroup']) > 3) {
    die('Please do not input more than 3 characters for the userGroup field!');
  }

  if (strlen($_POST['userGroupName']) > 50) {
    die('Please do not input more than 50 characters for the userGroupName field!');
  }

  if (strlen($_POST['userGroupDescription']) > 100) {
    die('Please do not input more than 100 characters for the userGroupDescription field!');
  }

  // Add to database
  $stmt = $conn->prepare('INSERT INTO userGroup (userGroup, userGroupName, userGroupDescription) VALUES (?,?,?)');
  $stmt->bind_param("sss",$_POST['userGroup'],$_POST['userGroupName'],$_POST['userGroupDescription']);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->free_result();
  $stmt->close();

  // Redirect to user group setting page
  // Cannot use header because some html have already been sent
	?>
	<script type="text/javascript">
		window.location = "settings_userGroup.php";
  </script>
	<?php
	die();

} else {

  // Display form

  ?>

  <div class="row"><div class="col s12">
    <h3>New user group</h3>
  </div></div>

  <div class="row">
    <form class="col s12 m12 l6" action="" method="post">

      <div class="row">
        <div class="input-field col s12">
          <input type="text" id="userGroup" name="userGroup" data-length="3">
          <label for="userGroup">User group ID</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input type="text" id="userGroupName" name="userGroupName" data-length="50">
          <label for="userGroupName">User group name</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <textarea class="materialize-textarea" id="userGroupDescription" name="userGroupDescription" data-length="100"></textarea>
          <label for="userGroupDescription">User group description</label>
        </div>
      </div>

      <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Create
      <i class="material-icons right">send</i>

    </form>
  </div>

  <?php

}
