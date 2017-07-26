<?php
// Lists the requested forum
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/css/materialize.min.css">
<!--Let browser know website is optimized for mobile-->
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/js/materialize.min.js"></script>

<!-- Initialize mobile sidenav-->
<script>
$(document).ready(function() {
		$(".button-collapse").sideNav();
  });
</script>

<?php

require "sidenav.php";

// Check if user requested a forum to display
// If not, redirect to index.php
if (!isset($_GET['fId'])) {
  header('Location: index.php');
  die();
}

// Check if requested forum exist
$stmt = $conn->prepare('SELECT fId FROM forum WHERE fId = ?');
$stmt->bind_param("s",$_GET['fId']);
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$stmt->error);
}

$stmt->bind_result($fId);
$stmt->fetch();

if ($fId !== $_GET['fId']) {
	die('The requested forum does not exist!');
}

$stmt->free_result();
$stmt->close();


// Check forum type then check permission accordingly
$stmt = $conn->prepare('SELECT hId FROM forum WHERE fId = ?');
$stmt->bind_param("s",$_GET['fId']);
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
    die('You do not have permission to view this forum!');
  }

  // If user only have VH permission
  if (havePermission(session_id(),"VH") AND !havePermission(session_id(),"VAH")) {

    // Check if the user's house and forum's house match
    if (getUserHId(session_id()) !== $hId) {
      die('You do not have permission to view this forum!');
    }

  }

}

// Count the number of threads
$stmt = $conn->prepare('SELECT COUNT(*) AS numT FROM thread WHERE fId = ?');
$stmt->bind_param("s",$_GET['fId']);
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$stmt->error);
}

$stmt->bind_result($numT);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

// Get user's rowsPerPage
$rowsPerPage = getUserSetting(session_id(),"rowsPerPage");

// Calculate how many pages are needed
$numPage = ceil($numT/$rowsPerPage);

// Get current page
if (!isset($_GET['page']) OR $_GET['page'] < 1) {
	$cPage = 1;
} else {
	$cPage = intval($_GET['page']);
}

// Pagination
echo '<div class="row"><div class="col s12">';
echo '<ul class="pagination">';

// Backwawrds button
// <li class="(disabled)"><a href="#!"><i class="material-icons">chevron_left</i></a></li>
echo '<li class="';
if ($cPage < 2) {
	echo 'disabled';
} else {
	echo 'waves-effect';
}
echo '">';
echo '<a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage - 1).'">';
echo '<i class="material-icons">chevron_left</i></a></li>';

// Display the page numbers of the current page, 3 pages before and 3 pages array_filter

// 3 pages before current page
if (($cPage - 3) > 0) {
	echo '<li class="waves-effect"><a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage - 3).'">'.($cPage - 3).'</a></li>';
}
if (($cPage - 2) > 0) {
	echo '<li class="waves-effect"><a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage - 2).'">'.($cPage - 2).'</a></li>';
}
if (($cPage - 1) > 0) {
	echo '<li class="waves-effect"><a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage - 1).'">'.($cPage - 1).'</a></li>';
}

// Current page
echo '<li class="active"><a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage).'">'.($cPage).'</a></li>';

// 3 pages after the current page
if (($cPage + 1) <= $numPage) {
	echo '<li class="waves-effect"><a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage + 1).'">'.($cPage + 1).'</a></li>';
}
if (($cPage + 2) <= $numPage) {
	echo '<li class="waves-effect"><a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage + 2).'">'.($cPage + 2).'</a></li>';
}
if (($cPage + 3) <= $numPage) {
	echo '<li class="waves-effect"><a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage + 3).'">'.($cPage + 3).'</a></li>';
}

// Forward button
// <li class="waves-effect"><a href="#!"><i class="material-icons">chevron_right</i></a></li>
echo '<li class="';
if ($cPage == $numPage) {
	echo 'disabled';
} else {
	echo 'waves-effect';
}
echo '">';
echo '<a href="viewforum.php?fId='.$_GET['fId'].'&page='.($cPage + 1).'">';
echo '<i class="material-icons">chevron_right</i></a></li>';

echo '</ul>';
echo '</div></div>';

// Get the thread listing of the current page
$stmt = $conn->prepare('SELECT t.tId,t.tTitle,t.tTime,t.studentId,u.userName FROM thread t JOIN users u ON t.studentId = u.studentId WHERE fId = ? ORDER BY tTime DESC LIMIT '.$rowsPerPage.' OFFSET '.($rowsPerPage * ($cPage - 1)).';');
$stmt->bind_param("s",$_GET['fId']);
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$stmt->error);
}

$stmt->bind_result($tId,$tTitle,$tTime,$studentId,$userName);

echo '<div class="row"><div class="col s12">';
echo '<table><thead><tr>';
echo '<th>Title</th>';
echo '<th class="hide-on-small-only">Time posted</th>';
echo '<th class="hide-on-small-only">Started by</th>';
echo '</tr></thead>';
echo '<tbody>';

while ($stmt->fetch()) {

	echo '<tr>';

	echo '<td>';
	echo '<a href="viewthread.php?tId='.$tId.'">';
	echo $tTitle;
	echo '</a></td>';

	echo '<td class="hide-on-small-only">';
	echo date('j/n/Y G:i',$tTime + $timezoneOffset);
	echo '</td>';

	echo '<td class="hide-on-small-only">';
	echo '<a href="profile.php?studentId='.$studentId.'">';
	echo $userName;
	echo '</a></td>';

	echo '</tr>';

}

$stmt->free_result();
$stmt->close();

echo '</tbody></table>';
echo '</div></div>';

// FAB to create new thread
echo '<div class="fixed-action-btn">';
echo '<a href="postthread.php?fId='.$_GET['fId'].'" class="btn-floating btn-large red waves-effect waves-light">';
echo '<i class="large material-icons">add</i>';
echo '</a></div>';

?>
