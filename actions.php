<?php
// Page to perform various action that does not need feedback, specified by get request
// Redirect after the action have been performed
// No html, just php


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

	// Check forum type then check permission accordingly
	$stmt = $conn->prepare('SELECT f.hId, t.fId FROM forum f JOIN thread t ON f.fId = t.fId WHERE t.tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
	  die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($hId,$fId);
	$stmt->fetch();

	$stmt->free_result();
	$stmt->close();

	if ($hId === NULL) {

	  // Check for RI permission
	  if (!havePermission(session_id(),"RI")) {
	    die('You do not have permission to perform this action!');
	  }

	} else {

	  // Check for RH or RAH permission
	  if (!havePermission(session_id(),"RH") AND !havePermission(session_id(),"RAH")) {
	    die('You do not have permission to perform this action!');
	  }

	  // If user only have RH permission
	  if (havePermission(session_id(),"RH") AND !havePermission(session_id(),"RAH")) {

	    // Check if the user's house and forum's house match
	    if (getUserHId(session_id()) !== $hId) {
	      die('You do not have permission to perform this action!');
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

	// Check if the user specified a thread
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

	// Check forum type then check permission accordingly
	$stmt = $conn->prepare('SELECT f.hId, t.fId FROM forum f JOIN thread t ON f.fId = t.fId WHERE t.tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
	  die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($hId,$fId);
	$stmt->fetch();

	$stmt->free_result();
	$stmt->close();

	if ($hId == NULL) {

		// Check for DI permission
		if (!havePermission(session_id(),"DI")) {
			die('You do not have permission to perform this action!');
		}

	} else {

		// Check for DH or DAH permission
		if (!havePermission(session_id(),"DH") AND !havePermission(session_id(),"DAH")) {
			die('You do not have permission to perform this action!');
		}

		// If user only have DH permission
		if (havePermission(session_id(),"DH") AND !havePermission(session_id(),"DAH")) {

			// Check if the user's house and forum's house match
			if (getUserHId(session_id()) !== $hId) {
				die('You do not have permission to perform this action!');
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

	$stmt->free_result();
	$stmt->close();

	// Delete the requested thread
	$stmt = $conn->prepare('DELETE FROM thread WHERE tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	header('Location: viewforum.php?fId='.$fId);
	die();

}

// Delete reply
if ($_GET['action'] == "rdelete") {

	// Check if the user specified a reply
	// If not, redirect to index.php
	if (!isset($_GET['rId'])) {
		header('Location: index.php');
		die();
	}

	// Check if the specified reply exist
	$stmt = $conn->prepare('SELECT rId FROM reply WHERE rId = ?');
	$stmt->bind_param("i",intval($_GET['rId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($rId);
	$stmt->fetch();

	if ($rId !== intval($_GET['rId'])) {
		die('The requested reply does not exist!');
	}

	$stmt->free_result();
	$stmt->close();

	// Get fId
	// Also get tId for redirect
	// Also get hId for checking permission
	$stmt = $conn->prepare('SELECT t.fId, r.tId, f.hId FROM thread t JOIN reply r ON t.tId = r.tId JOIN forum f ON t.fId = f.fId WHERE r.rId = ?');
	$stmt->bind_param("i",intval($_GET['rId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($fId,$tId,$hId);
	$stmt->fetch();

	$stmt->free_result();
	$stmt->close();

	if ($hId == NULL) {

		// Check for DI permission
		if (!havePermission(session_id(),"DI")) {
			die('You do not have permission to perform this action!');
		}

	} else {

		// Check for DH or DAH permission
		if (!havePermission(session_id(),"DH") AND !havePermission(session_id(),"DAH")) {
			die('You do not have permission to perform this action!');
		}

		// If user only have DH permission
		if (havePermission(session_id(),"DH") AND !havePermission(session_id(),"DAH")) {

			// Check if the user's house and forum's house match
			if (getUserHId(session_id()) !== $hId) {
				die('You do not have permission to perform this action!');
			}

		}

	}

	// Delete the requested reply
	$stmt = $conn->prepare('DELETE FROM reply WHERE rId = ?');
	$stmt->bind_param("i",intval($_GET['rId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	// Redirect to the thread
	header('Location: viewthread.php?tId='.$tId);
	die();

}

// Pin thread
if ($_GET['action'] == "pin") {

	// Check if user specified anything
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

	// Check forum type then check permission accordingly
	$stmt = $conn->prepare('SELECT f.hId, t.fId FROM forum f JOIN thread t ON f.fId = t.fId WHERE t.tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
	  die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($hId,$fId);
	$stmt->fetch();

	$stmt->free_result();
	$stmt->close();

	if ($hId == NULL) {

		// Check for EI permission
		if (!havePermission(session_id(),"EI")) {
			die('You do not have permission to perform this action!');
		}

	} else {

		// Check for EH or EAH permission
		if (!havePermission(session_id(),"EH") AND !havePermission(session_id(),"EAH")) {
			die('You do not have permission to perform this action!');
		}

		// If user only have EH permission
		if (havePermission(session_id(),"EH") AND !havePermission(session_id(),"EAH")) {

			// Check if the user's house and forum's house match
			if (getUserHId(session_id()) !== $hId) {
				die('You do not have permission to perform this action!');
			}

		}

	}

	// Pin the thread
	$stmt = $conn->prepare('UPDATE thread SET pin = "1" WHERE tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	header('Location: viewthread.php?tId='.$_GET['tId']);
	die();

}

// Unpin thread
if ($_GET['action'] == "unpin") {

	// Check if user specified anything
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

	// Check forum type then check permission accordingly
	$stmt = $conn->prepare('SELECT f.hId, t.fId FROM forum f JOIN thread t ON f.fId = t.fId WHERE t.tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
	  die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($hId,$fId);
	$stmt->fetch();

	$stmt->free_result();
	$stmt->close();

	if ($hId == NULL) {

		// Check for EI permission
		if (!havePermission(session_id(),"EI")) {
			die('You do not have permission to perform this action!');
		}

	} else {

		// Check for EH or EAH permission
		if (!havePermission(session_id(),"EH") AND !havePermission(session_id(),"EAH")) {
			die('You do not have permission to perform this action!');
		}

		// If user only have EH permission
		if (havePermission(session_id(),"EH") AND !havePermission(session_id(),"EAH")) {

			// Check if the user's house and forum's house match
			if (getUserHId(session_id()) !== $hId) {
				die('You do not have permission to perform this action!');
			}

		}

	}

	// unpin the thread
	$stmt = $conn->prepare('UPDATE thread SET pin = "0" WHERE tId = ?');
	$stmt->bind_param("i",intval($_GET['tId']));
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	header('Location: viewthread.php?tId='.$_GET['tId']);
	die();

}

// Allow a permission for a userGroup
if ($_GET['action'] == "perm_allow") {

	// Check parameters
	// If not complete, redirect to index.php
	if (!isset($_GET['userGroup']) OR !isset($_GET['permission'])) {
		header('Location: index.php');
		die();
	}

	// Check if userGroup exist
	$stmt = $conn->prepare('SELECT userGroup FROM userGroup WHERE userGroup = ?');
	$stmt->bind_param("s",$_GET['userGroup']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($userGroup);
	$stmt->fetch();

	if ($userGroup !== $_GET['userGroup']) {
		die('The requested userGroup does not exist!');
	}

	$stmt->free_result();
	$stmt->close();

	// Check if permission exists
	$stmt = $conn->prepare('SELECT permission FROM permission WHERE permission = ?');
	$stmt->bind_param("s",$_GET['permission']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($permission);
	$stmt->fetch();

	if ($permission !== $_GET['permission']) {
		die('The requested permission does not exist!');
	}

	$stmt->free_result();
	$stmt->close();

	// Check permission
	if (!havePermission(session_id(),"AUG")) {
		die('You do not have permission to perform this action!');
	}

	// Add db record
	$stmt = $conn->prepare('INSERT INTO userPermission (userGroup, permission) VALUES (?,?)');
	$stmt->bind_param("ss",$_GET['userGroup'],$_GET['permission']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	// Redirect to userGroup edit page
	header('Location: settings_userGroup_edit.php?userGroup='.$_GET['userGroup']);
	die();

}

// Disallow a permission for a userGroup
if ($_GET['action'] == "perm_disallow") {

	// Check parameters
	// If not complete, redirect to index.php
	if (!isset($_GET['userGroup']) OR !isset($_GET['permission'])) {
		header('Location: index.php');
		die();
	}

	// Check if userGroup exist
	$stmt = $conn->prepare('SELECT userGroup FROM userGroup WHERE userGroup = ?');
	$stmt->bind_param("s",$_GET['userGroup']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($userGroup);
	$stmt->fetch();

	if ($userGroup !== $_GET['userGroup']) {
		die('The requested userGroup does not exist!');
	}

	$stmt->free_result();
	$stmt->close();

	// Check if permission exists
	$stmt = $conn->prepare('SELECT permission FROM permission WHERE permission = ?');
	$stmt->bind_param("s",$_GET['permission']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($permission);
	$stmt->fetch();

	if ($permission !== $_GET['permission']) {
		die('The requested permission does not exist!');
	}

	$stmt->free_result();
	$stmt->close();

	// Check permission
	if (!havePermission(session_id(),"AUG")) {
		die('You do not have permission to perform this action!');
	}

	// Remove from db
	$stmt = $conn->prepare('DELETE FROM userPermission WHERE userGroup = ? AND permission = ?');
	$stmt->bind_param("ss",$_GET['userGroup'],$_GET['permission']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	// Redirect to userGroup edit page
	header('Location: settings_userGroup_edit.php?userGroup='.$_GET['userGroup']);
	die();

}

// Enable two-factor auth
if ($_GET['action'] == "tfa_enable") {

	$studentId = getStudentId(session_id());

	// Check if all parameters are set
	if ((strlen($_POST['password']) < 1) or (strlen($_POST['otp']) < 1) or ($_POST['submit'] !== "submit")) {
		die('Please fill in all the fields!');
	}

	if (strlen($_SESSION['tfa_secret']) < 1) {
		die('Tfa secret not set!');
	}

	// Check if tfa is disabled
	$stmt = $conn->prepare('SELECT studentId FROM tfa WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($qStudentId);
	$stmt->fetch();
	$stmt->free_result();
	$stmt->close();

	if ($qStudentId == $studentId) {
		die('You have already enabled tfa!');
	}

	// Verify passowrd
	$stmt = $conn->prepare('SELECT hash FROM users WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($qHash);
	$stmt->fetch();
	$stmt->free_result();
	$stmt->close();

	if (!password_verify($_POST['password'],$qHash)) {
		die('Password incorrect!');
	}

	// Verify otp
	$result = $tfa->verifyCode($_SESSION['tfa_secret'],$_POST['otp']);
	if (!$result) {
		die('OTP verification failed!');
	}

	// Verified, store secret
	$stmt = $conn->prepare('INSERT INTO tfa (studentId,tfaSecret) VALUES (?,?)');
	$stmt->bind_param("ss",$studentId,$_SESSION['tfa_secret']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	// Unset session variable
	unset($_SESSION['tfa_secret']);

	// Redirect to tfa settings page
	header('Location: settings_user_tfa.php');
	die();

}

if ($_GET['action'] == "tfa_disable") {

	$studentId = getStudentId(session_id());

	// Check if all parameters are set
	if ((strlen($_POST['pass']) < 1) or (strlen($_POST['otp']) < 1) or ($_POST['submit'] !== "submit")) {
		die('Please fill in all the fields!');
	}

	// Check if tfa is enabled
	$stmt = $conn->prepare('SELECT studentId, tfaSecret FROM tfa WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($qStudentId,$tfaSecret);
	$stmt->fetch();
	$stmt->free_result();
	$stmt->close();

	if ($qStudentId !== $studentId) {
		die('You have not enabled tfa yet!');
	}

	// Verify passowrd
	$stmt = $conn->prepare('SELECT hash FROM users WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($qHash);
	$stmt->fetch();
	$stmt->free_result();
	$stmt->close();

	if (!password_verify($_POST['pass'],$qHash)) {
		die('Password incorrect!');
	}

	// Verify otp
	$result = $tfa->verifyCode($tfaSecret,$_POST['otp']);
	if (!$result) {
		die('OTP verification failed!');
	}

	// Verified, disable tfa
	$stmt = $conn->prepare('DELETE FROM tfa WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	// Redirect to tfa settings page
	header('Location: settings_user_tfa.php');
	die();

}

if ($_GET['action'] == "email_verify") {

	// Check parameters
	if ((strlen($_GET['token']) < 1) OR (strlen($_GET['email']) < 1)) {
		header('Location: index.php');
		die();
	}

	// Get studentId
	$studentId = getStudentId(session_id());

	// Get info from db
	$stmt = $conn->prepare('SELECT token, action, studentId FROM mailToken WHERE token = ?');
	$stmt->bind_param("s",$_GET['token']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->bind_result($token,$action,$qStudentId);
	$stmt->fetch();
	$stmt->free_result();
	$stmt->close();

	// Validate token
	if ($token !== $_GET['token']) {
		die('Invalid token!');
	}

	// Check action
	if ($action !== 'verify') {
		die('Wrong action!');
	}

	// Check studentId
	if ($studentId !== $qStudentId) {
		die('Wrong studentId!');
	}

	// Check email
	if ($_GET['email'] !== getUserEmail(session_id())) {
		die('Wrong email!');
	}

	// Verified, update database
	$stmt = $conn->prepare('UPDATE users SET emailVerified = 1 WHERE studentId = ?');
	$stmt->bind_param("s",$studentId);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	// Delete token
	$stmt = $conn->prepare('DELETE FROM mailToken WHERE token = ?');
	$stmt->bind_param("s",$_GET['token']);
	$result = $stmt->execute();
	if (!$result) {
		die('Query failed. '.$stmt->error);
	}

	$stmt->free_result();
	$stmt->close();

	die('Email verified');

}

// No action was performed, redirect to index.php
header('Location: login.php');
die();

?>
