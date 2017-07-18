<?php
// Index page, require login

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
  session_unset();
	mysqli_free_result($result);
  $sql = 'DELETE FROM session WHERE sessionId = "'.session_id().'";';
  $conn->query($sql);
  // No need to check result here as well
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
<body onload="showSideNav();">
<!--Import jQuery before materialize.js-->
<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.99.0/js/materialize.min.js"></script>

<!-- Initialize mobile sidenav-->
<script>
$(document).ready(function() {
		$(".button-collapse").sideNav();
  });
function showSideNav() {
	$('.button-collapse').sideNav('show');
}
</script>

<nav>
  <div class="nav-wrapper purple lighten-2">
		<div class="row">
			<div class="col s12">
				<!-- Logo -->
    		<a href="index.php" class="brand-logo center flow-text">House Forums</a>

				<!-- Sidenav -->
				<ul id="slide-out" class="side-nav">

					<!-- User-view -->
					<li><div class="user-view">
						<div class="background">
							<img src="https://puu.sh/vutY0.jpg">
						</div>
						<a href="profile.php"><img class="circle" src="https://puu.sh/wFuFj.jpg"></a>
						<span class="white-text name"><?php echoGetUserName(session_id()); ?> (<?php echoGetUserGroupName(session_id()); ?>)</span>
						<span class="white-text email"><?php echoGetStudentId(session_id()); ?></span>
					</div></li>

					<!-- Menu -->
					<?php

					// Show house-specific forum link(s)

					// If the user only have permission to view one house-specific forum (the one they belong to)
					if (havePermission(session_id(),"VH")) {

						echo '<li><a href="index.php" class="waves-effect"><i class="material-icons">chat</i>'.getUserHouseName(session_id()).' House Forum</a></li>';

					} elseif (havePermission(session_id(),"VAH")) { // If user have permission to view all houses' forums

						// Find all house forums
						$sql = 'SELECT fId, fName FROM forum WHERE hId IS NOT NULL';
						$result = $conn->query($sql);
						if (!$result) {
							die('Query failed. '.$conn->error);
						}

						// List all the house forums
						while($row = mysqli_fetch_assoc($result)) {

							echo '<li><a href="viewforum.php?fId='.$row['fId'].'" class="waves-effect"><i class="material-icons">chat</i>'.$row['fName'].'</a></li>';

						}

					}

					// Show inter-house forum link
					if (havePermission(session_id(),"VI")) {
						echo '<li><a href="viewforum.php?fId=IHF" class="waves-effect"><i class="material-icons">forum</i>Inter-house Forum</a></li>';
					}

					// Divider
					echo '<li><div class="divider"></div></li>';

					?>

					<li><a href="logout.php" class="waves-effect"><i class="material-icons">exit_to_app</i>Logout</a></li>
				</ul>
				<a href="#" data-activates="slide-out" class="button-collapse show-on-large"><i class="material-icons">menu</i></a>

			</div>
		</div>
  </div>
</nav>


<?php

// Check if user have permission to view this forum
if (!havePermission(session_id(),"VH") AND !havePermission(session_id(),"VAH")) {
	die('You do not have permission to view this forum');
}

// Get hId of current session
$hId = getUserHId(session_id());

// Find the relative forum of the house the user belongs to
$sql = 'SELECT fId FROM forum WHERE hId = "'.$hId.'"';
$result = $conn->query($sql);
if (!$result) {
	die('Query failed. '.$conn->error);
}

$row = mysqli_fetch_assoc($result);
$fId = $row['fId'];

mysqli_free_result($result);

// Count the number of threads
$sql = 'SELECT COUNT(*) AS numT FROM thread WHERE fId = "'.$fId.'";';
$result = $conn->query($sql);
if (!$result) {
	die('Query failed. '.$conn->error);
}

$row = mysqli_fetch_assoc($result);
$numT = $row['numT'];

mysqli_free_result($result);

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
echo '<a href="index.php?page='.($cPage - 1).'">';
echo '<i class="material-icons">chevron_left</i></a></li>';

// Display the page numbers of the current page, 3 pages before and 3 pages array_filter

// 3 pages before current page
if (($cPage - 3) > 0) {
	echo '<li class="waves-effect"><a href="index.php?page='.($cPage - 3).'">'.($cPage - 3).'</a></li>';
}
if (($cPage - 2) > 0) {
	echo '<li class="waves-effect"><a href="index.php?page='.($cPage - 2).'">'.($cPage - 2).'</a></li>';
}
if (($cPage - 1) > 0) {
	echo '<li class="waves-effect"><a href="index.php?page='.($cPage - 1).'">'.($cPage - 1).'</a></li>';
}

// Current page
echo '<li class="active"><a href="index.php?page='.($cPage).'">'.($cPage).'</a></li>';

// 3 pages after the current page
if (($cPage + 1) <= $numPage) {
	echo '<li class="waves-effect"><a href="index.php?page='.($cPage + 1).'">'.($cPage + 1).'</a></li>';
}
if (($cPage + 2) <= $numPage) {
	echo '<li class="waves-effect"><a href="index.php?page='.($cPage + 2).'">'.($cPage + 2).'</a></li>';
}
if (($cPage + 3) <= $numPage) {
	echo '<li class="waves-effect"><a href="index.php?page='.($cPage + 3).'">'.($cPage + 3).'</a></li>';
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
echo '<a href="index.php?page='.($cPage + 1).'">';
echo '<i class="material-icons">chevron_right</i></a></li>';

echo '</ul>';
echo '</div></div>';

// Get the thread listing of the current page
$sql = 'SELECT * FROM thread WHERE fId = "'.$fId.'" ORDER BY tTime DESC LIMIT '.$rowsPerPage.' OFFSET '.($rowsPerPage * ($cPage - 1)).';';
$result = $conn->query($sql);
if (!$result) {
	die('Query failed. '.$conn->error);
}

echo '<div class="row"><div class="col s12">';
echo '<table><thead><tr>';
echo '<th>Title</th>';
echo '<th class="hide-on-small-only">Time posted</th>';
echo '<th class="hide-on-small-only">Started by</th>';
echo '</tr></thead>';
echo '<tbody>';

while ($row = mysqli_fetch_assoc($result)) {

	echo '<tr>';

	echo '<td>';
	echo '<a href="">';
	echo $row['tTitle'];
	echo '</a></td>';

	echo '<td class="hide-on-small-only">';
	echo date('j/n/Y G:i',$row['tTime'] + $timezoneOffset);
	echo '</td>';

	echo '<td class="hide-on-small-only">';
	echo '<a href="">';
	echo userNameFromStudentId($row['studentId']);
	echo '</a></td>';

	echo '</tr>';

}

mysqli_free_result($result);

echo '</tbody></table>';
echo '</div></div>';

?>
