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

// Check if user requested anything
// If not, redirect to index.php
if (!isset($_GET['userGroup'])) {
  // Cannot use header because some html have already been sent
  ?>
  <script type="text/javascript">
    window.location = "index.php";
  </script>
  <?php
  die();
}

// Check if the requested userGroup exists
// Also get userGroupName for later use
$stmt = $conn->prepare('SELECT userGroup, userGroupName FROM userGroup WHERE userGroup = ?');
$stmt->bind_param("s",$_GET['userGroup']);
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->error);
}

$stmt->bind_result($userGroup,$userGroupName);
$stmt->fetch();

if ($userGroup !== $_GET['userGroup']) {
  die('The requested userGroup does not exist!');
}

$stmt->free_result();
$stmt->close();

// Check permission
if (!havePermission(session_id(),"AUG")) {
  die('You do not have permission to perform this action!');
}

// Get the permissions of the requested userGroup
$stmt = $conn->prepare('SELECT permission FROM userPermission WHERE userGroup = ?');
$stmt->bind_param("s",$_GET['userGroup']);
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->error);
}

$stmt->bind_result($permission);

$permArray = array();

while ($stmt->fetch()) {

  // Append the permission to array
  array_push($permArray,$permission);

}

$stmt->free_result();
$stmt->close();

// Function to search the array and return TRUE or FALSE
function groupHavePermission($perm) {

  global $permArray;

  $result = array_search($perm,$permArray);

  // array_search returns the array key of the result
  // Cannot compare with if (!$result) because return value of 0 will be interpreted as FALSE
  if ($result === FALSE) {
    return FALSE;
  } else {
    return TRUE;
  }

}

// Wrapper function to echo result of groupHavePermission, but in english
function echoGroupHavePermission($perm) {

  $return = groupHavePermission($perm);

  if ($return) {
    return '<span class="green-text">Allow</span>';
  } else {
    return '<span class="red-text">Disallow</span>';
  }

}

// Get the permissions
$sql = 'SELECT permission, permissionDescription FROM permission';
$result = $conn->query($sql);
if (!$result) {
  die('Query failed. '.$conn->error);
}

?>

<div class="row"><div class="col s12">
  <h3>Editing user group: <b><?php echo $_GET['userGroup']; ?> (<?php echo $userGroupName; ?>)</b></h3>
</div></div>

<div class="row">
  <div class="col s12 m12 l6">
    <div class="card purple lighten-2">
      <div class="card-content white-text">
        <span class="card-title">Note</span>
        <p>If the permission *AH was to be allowed, please disallow the corresponding *H permission.</p>
        <p>For example, if VAH was allowed, VH should be disallowed for that user group.</p>
      </div>
    </div>
  </div>
</div>

<div class="row"><div class="col s12"><table>
<thead>
  <tr>
    <th>Permission ID</th>
    <th>Description</th>
    <th>Status</th>
    <th>Action</th>
  </tr>
</thead>

<tbody>

<?php

while ($row = mysqli_fetch_assoc($result)) {

  echo '<tr>';

  echo '<td>'.$row['permission'].'</td>';
  echo '<td>'.$row['permissionDescription'].'</td>';
  echo '<td>'.echoGroupHavePermission($row['permission']).'</td>';

  // Allow or disallow button
  if (groupHavePermission($row['permission'])) {
    echo '<td><a class="btn waves-effect waves-light red lighten-2" href="actions.php?action=perm_disallow&userGroup='.$_GET['userGroup'].'&permission='.$row['permission'].'"><i class="material-icons">clear</i><span class="hide-on-small-only">Disallow</span></a></td>';
  } else {
    echo '<td><a class="btn waves-effect waves-light green lighten-2" href="actions.php?action=perm_allow&userGroup='.$_GET['userGroup'].'&permission='.$row['permission'].'"><i class="material-icons">done</i><span class="hide-on-small-only">Allow</span></a></td>';
  }

  echo '</tr>';

}

?>

</tbody></table>
</div></div>

<?php

mysqli_free_result($result);
