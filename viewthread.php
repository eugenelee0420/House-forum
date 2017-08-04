<?php
// Shows the requested thread
// Require login and sufficient permission (varies with forums)

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
		$('.collapsible').collapsible();
  });
</script>

<?php

require "sidenav.php";

// Check if user requested a thread to display
// If not, redirect to index.php
if (!isset($_GET['tId'])) {
	// Cannot use header because some html have already been sent
	?>
	<script type="text/javascript">
		window.location = "index.php";
  </script>
	<?php
	die();
}

// Check if the requested thread exist
$stmt = $conn->prepare('SELECT tId FROM thread WHERE tId = ?');
$stmt->bind_param("i",intval($_GET['tId']));
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$stmt->error);
}

$stmt->bind_result($tId);
$stmt->fetch();

if ($tId !== intval($_GET['tId'])) {
	die('The requested thread does not exist!');
}

$stmt->free_result();
$stmt->close();

// Get the fId that this thread belongs to
$stmt = $conn->prepare('SELECT fId FROM thread WHERE tId = ?');
$stmt->bind_param("i",intval($_GET['tId']));
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$stmt->error);
}

$stmt->bind_result($fId);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

// Check forum type then check permission accordingly
$stmt = $conn->prepare('SELECT hId FROM forum WHERE fId = ?');
$stmt->bind_param("s",$fId);
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->error);
}

$stmt->bind_result($hId);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

if ($hId === NULL) {

  // Check for VI permission
  if (!havePermission(session_id(),"VI")) {
    die('You do not have permission to view this forum');
  }

} else {

  // Check for VH or VAH permission
  if (!havePermission(session_id(),"VH") AND !havePermission(session_id(),"VAH")) {
    die('You do not have permission to view this forum');
  }

  // If user only have VH permission
  if (havePermission(session_id(),"VH") AND !havePermission(session_id(),"VAH")) {

    // Check if the user's house and forum's house match
    if (getUserHId(session_id()) !== $hId) {
      die('You do not have permission to view this forum');
    }

  }

}

// Get the thread contents
$stmt = $conn->prepare('SELECT t.tTitle, t.tContent, t.tTime, t.fId, f.fName, t.studentId FROM thread t JOIN forum f ON t.fId = f.fId WHERE t.tId = ?');
$stmt->bind_param("i",intval($_GET['tId']));
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$conn->error);
}

$stmt->bind_result($tTitle,$tContent,$tTime,$fId,$fName,$studentId);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

// Parse markdown
$mdContent = $parsedown->text($tContent);

// Display the thread content
echo '<font face="roboto">';
echo '<div class="row"><div class="col s12">';

// Breadcrumbs
echo '<br><nav><div class="nav-wrapper"><div class="col s12">';
echo '<a href="viewforum.php?fId='.$fId.'" class="breadcrumb">'.$fName.'</a>';
echo '<a href="" class="breadcrumb">'.$tTitle.'</a>';
echo '</div></div></nav>';

echo '<div class="row"><div class="col s12">';

echo '<h3>'.$tTitle.'</h3>';
echo '<p class="grey-text">Started by <a href="profile.php?studentId='.$studentId.'">'.userNameFromStudentId($studentId).'</a> on '.date('j/n/Y G:i',$tTime + $timezoneOffset).'</p>';

echo '<div class="flow-text">'.$mdContent.'</div>';

echo '</div></div>';

echo '<hr>';

// Get replies
$stmt = $conn->prepare('SELECT r.rId, r.rContent, r.rTime, r.studentId, u.userName FROM reply r JOIN users u ON r.studentId = u.studentId WHERE r.tId = ? ORDER BY r.rTime ASC');
$stmt->bind_param("i",intval($_GET['tId']));
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$stmt->error);
}

$stmt->bind_result($rId, $rContent, $rTime, $studentId, $userName);

// Collapsible
echo '<div class="row"><div class="col s12">';
echo '<ul class="collapsible" data-collapsible="expandable">';

// Set up counter
$x = 1;

// Store the results so $stmt->num_rows can return a number
$stmt->store_result();
// Get the number of rows
$numrow = $stmt->num_rows;

// Functions to display buttons to delete or edit reply
function displayBtnEdit($rId) {
	echo '<a href="edit.php?rId='.$rId.'" class="btn-floating tooltipped red" data-tooltip="Edit reply" data-position="down" data-delay="0"><i class="material-icons">edit</i></a>';
}
function displayBtnDelete($rId) {
	echo ' ';
	echo '<a href="actions.php?action=rdelete&rId='.$rId.'" class="btn-floating tooltipped yellow darken-1" data-tooltip="Delete reply" data-position="down" data-delay="0"><i class="material-icons">delete</i></a>';
}

// Display the replies
while ($stmt->fetch()) {

	echo '<li>';

	// Expand the reply if this is the last row
	if ($x == $numrow) {
		echo '<div class="collapsible-header active">'.$userName.'</div>';
	} else {
		echo '<div class="collapsible-header">'.$userName.'</div>';
	}

	// Parse markdown
	$mdReply = $parsedown->text($rContent);

	echo '<div class="collapsible-body">';
	echo '<p class="grey-text"><a href="profile.php?studentId='.$studentId.'">'.$userName.'</a> replied on '.date('j/n/Y G:i',$rTime + $timezoneOffset).'</p>';
	echo '<div class="flow-text">'.$mdReply.'</div>';
	echo '<p>';

	// Display buttons based on permissions
	if ($hId == NULL) {

		if (havePermission(session_id(),"EI")) {
			displayBtnEdit($rId);
		}

		if (havePermission(session_id(),"DI")) {
			displayBtnDelete($rId);
		}

	} else {

		if (havePermission(session_id(),"EH") OR havePermission(session_id(),"EAH")) {
			displayBtnEdit($rId);
		}

		if (havePermission(session_id(),"DH") OR havePermission(session_id(),"DAH")) {
			displayBtnDelete($rId);
		}

	}

	echo '</p>';
	echo '</li>';

	// Increment counter
	$x++;

}

echo '</ul>';
echo '</div></div>';

$stmt->free_result();
$stmt->close();

// Function to display post reply form
// Because this form will appear in code multiple times, but will only be called once
function displayForm() {

	global $_GET;

	echo
	'<hr>

	<div class="row">
		<form class="col s12" action="actions.php?tId='.$_GET['tId'].'&action=reply" method="post">

			<div class="row">
				<div class="input-field col s12">
					<textarea id="reply" name="reply" class="materialize-textarea" data-length="65535"></textarea>
					<label for="reply">Your reply</label>
				</div>
			</div>

			<div class="row">
				<div class="col s12">
					<button class="btn waves-effect waves-light" type="submit" name="submit" value="submit">Post reply
					<i class="material-icons right">send</i></button>
				</div>
			</div>

		</form>
	</div>';

}

if ($hId == NULL) {

	if (havePermission(session_id(),"RI")) {
		displayForm();
	}

} else {

	if (havePermission(session_id(),"RH") OR havePermission(session_id(),"RAH")) {
		displayForm();
	}

}

// Functions to display buttons in FAB
function displayFABEdit() {
	echo '<li><a href="edit.php?tId='.$_GET['tId'].'" class="btn-floating tooltipped red" data-tooltip="Edit thread" data-position="left" data-delay="0"><i class="material-icons">edit</i></a></li>';
}
function displayFABDelete() {
	echo '<li><a href="actions.php?action=delete&tId='.$_GET['tId'].'" class="btn-floating tooltipped yellow darken-1" data-tooltip="Delete thread" data-position="left" data-delay="0"><i class="material-icons">delete</i></a></li>';
}
function displayFABPin() {
	echo '<li><a class="btn-floating tooltipped blue" data-tooltip="Pin thread" data-position="left" data-delay="0"><i class="material-icons">label</i></a></li>';
}
function displayFABUnpin() {
	echo '<li><a class="btn-floating tooltipped blue" data-tooltip="Unpin thread" data-position="left" data-delay="0"><i class="material-icons">label_outline</i></a></li>';
}

// If user have either one permission to delete or edit
// If hId is null, either EI or DI must be true
// If hId is not null, either EAH or EH or DAH or DH must be true
if (( ($hId == NULL) AND (havePermission(session_id(),"EI") OR havePermission(session_id(),"DI")) ) OR ( ($hId !== NULL) AND (havePermission(session_id(),"EAH") OR havePermission(session_id(),"EH") OR havePermission(session_id(),"DAH") OR havePermission(session_id(),"DH") ))) {

	// Display the "more" icon/FAB
	echo '<div class="fixed-action-btn">';
	echo '<a class="btn-floating btn-large red">';
	echo '<i class="large material-icons">more_vert</i>';
	echo '</a>';

	echo '<ul>';
	// Display sub-fab based on permissions
	if ($hId == NULL) {

		if (havePermission(session_id(),"EI")) {
			displayFABEdit();
		}

		if (havePermission(session_id(),"DI")) {
			displayFABDelete();
		}

	} else {

		if (havePermission(session_id(),"EH") OR havePermission(session_id(),"EAH")) {
			displayFABEdit();
		}

		if (havePermission(session_id(),"DH") OR havePermission(session_id(),"DAH")) {
			displayFABDelete();
		}

	}

	echo '</ul>';
	echo '</div>';

}

?>

<!-- <div class="fixed-action-btn">
    <a class="btn-floating btn-large red">
      <i class="large material-icons">more_vert</i>
    </a>
    <ul>
      <li><a class="btn-floating tooltipped red" data-tooltip="Edit thread" data-position="left" data-delay="0"><i class="material-icons">edit</i></a></li>
      <li><a class="btn-floating tooltipped yellow darken-1" data-tooltip="Delete thread" data-position="left" data-delay="0"><i class="material-icons">delete</i></a></li>
      <li><a class="btn-floating tooltipped blue" data-tooltip="Pin thread" data-position="left" data-delay="0"><i class="material-icons">label</i></a></li>
    </ul>
  </div> -->
