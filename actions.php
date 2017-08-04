<?php
// Page to perform various action that does not need feedback, specified by get request
// Redirect after the action have been performed
// No html, just php

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

// Check if any action is requested
// If not, redirect to index.php
if (!isset($_GET['action'])) {
	header('Location: login.php');
	die();
}

// Reply
if ($_GET['action'] == "reply") {

	// Check if user requested anything
	// If not, redirect to index.php
	if (!isset($_GET['tId']) OR !isset($_POST['submit']) OR !isset($_POST['reply'])) {
		header('Location: login.php');
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

	  // Check for RI permission
	  if (!havePermission(session_id(),"RI")) {
	    die('You do not have permission to reply to this thread');
	  }

	} else {

	  // Check for RH or RAH permission
	  if (!havePermission(session_id(),"RH") AND !havePermission(session_id(),"RAH")) {
	    die('You do not have permission to reply to this thread');
	  }

	  // If user only have RH permission
	  if (havePermission(session_id(),"RH") AND !havePermission(session_id(),"RAH")) {

	    // Check if the user's house and forum's house match
	    if (getUserHId(session_id()) !== $hId) {
	      die('You do not have permission to reply to this thread');
	    }

	  }

	}


	// Check if reply is empty
	if (strlen($_POST['reply']) < 1) {
	  die('The reply cannot be empty!');
	}

	// Check field constraint
	if (strlen($_POST['reply']) > 65535) {
	  die('Please do not enter more than 65,535 characters for the reply!');
	}

	// Strip html tags
	$reply = strip_tags($_POST['reply']);

	// Get max rId
	$sql = 'SELECT MAX(rId) AS rMax FROM reply';
	$result = $conn->query($sql);
	if (!$result) {
	  die('Query failed. '.$conn->error);
	}

	$row = mysqli_fetch_assoc($result);
	$rId = (intval($row['rMax']) + 1);

	$stmt = $conn->prepare('INSERT INTO reply (rId, rContent, rTime, tId, studentId) VALUES ('.$rId.', ?, "'.time().'", ?, ?)');
	$stmt->bind_param("sis",$reply,intval($_GET['tId']),getStudentId(session_id()));
	$result = $stmt->execute();
	if (!$result) {
	  die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	header('Location: viewthread.php?tId='.$_GET['tId']);
	die();


}

// Delete thread
if ($_GET['action'] == "delete") {

	// Check if the user requested anything
	// If not, redirect to index.php
	if (!isset($_GET['tId'])) {
		header('Location: index.php');
		die();
	}

	// Check if the thread exist
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

	if ($hId == NULL) {

		// Check for DI permission
		if (!havePermission(session_id(),"DI")) {
			die('You do not have permission to delete this thread!');
		}

	} else {

		// Check for DH or DAH permission
		if (!havePermission(session_id(),"DH") AND !havePermission(session_id(),"DAH")) {
			die('You do not have permission to delete this thread!');
		}

		// If user only have DH permission
		if (havePermission(session_id(),"DH") AND !havePermission(session_id(),"DAH")) {

			// Check if the user's house and forum's house match
			if (getUserHId(session_id()) !== $hId) {
				die('You do not have permission to delete this thread!');
			}

		}

	}

	// Delete the replies associated with the deleted thread
	// Because foreign key constraint this must be deleted first
	$stmt = $conn->prepare('DELETE FROM reply WHERE tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	// Delete the requested thread
	$stmt = $conn->prepare('DELETE FROM thread WHERE tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();



	$stmt->free_result();
	$stmt->close();

	header('Location: viewforum.php?fId='.$fId);
	die();

}

// No action was performed, redirect to index.php
header('Location: login.php');
die();

?>
