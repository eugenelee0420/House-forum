<?php
// Page to edit thread or reply
// Require login and sufficient permission
// Self-submitting forms
// Because editing thread and replies require the same permission, by combining the two, permission check can be performed once only

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
    // Trigger autoresize for the forms
    $('#tContent').trigger('autoresize');
    $('#reply').trigger('autoresize');
  });
</script>

<?php

require "sidenav.php";

// Check if user requested anything
// If not, redirect to index.php
if (!isset($_GET['tId']) AND !isset($_GET['rId'])) {
  // Cannot use header because some html have already been sent
	?>
	<script type="text/javascript">
		window.location = "index.php";
  </script>
	<?php
	die();
}

// Set up the cases
if (isset($_GET['tId'])) {

  $field = 'tId';
  $table = 'thread';

  $tId = $_GET['tId'];

} elseif (isset($_GET['rId'])) {

  $field = 'rId';
  $table = 'reply';

  // leave tId unset, get after checking the reply exist

}

// Check if thread/reply exist
$stmt = $conn->prepare('SELECT '.$field.' FROM '.$table.' WHERE '.$field.' = ?');
$stmt->bind_param("i",intval($_GET[$field]));
$result = $stmt->execute();
if (!$result) {
  die('Query failed. '.$stmt->error);
}

$stmt->bind_result($theId);
$stmt->fetch();

if ($theId !== intval($_GET[$field])) {
  die('The requested '.$table.' does not exist!');
}

$stmt->free_result();
$stmt->close();

// Get tId (for reply)
if (!isset($tId)) {
  $stmt = $conn->prepare('SELECT tId FROM reply WHERE rId = ?');
  $stmt->bind_param("i",intval($_GET['rId']));
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->bind_result($tId);
  $stmt->fetch();

  $stmt->free_result();
  $stmt->close();
}

// Check forum type then check permission accordingly
$stmt = $conn->prepare('SELECT f.hId, t.fId FROM forum f JOIN thread t ON f.fId = t.fId WHERE t.tId = ?');
$stmt->bind_param("i",intval($tId));
$result = $stmt->execute();
if (!$result) {
	die('Query failed. '.$stmt->error);
}

$stmt->bind_result($hId,$fId);
$stmt->fetch();

$stmt->free_result();
$stmt->close();

if ($hId === NULL) {

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

// Check if form is submitted
if ($_POST['submit'] == 'submit') {

  // Form submitted, process form
  if (isset($_GET['tId'])) {

    // Check if all the fields are filled in
    if ((strlen($_POST['tTitle']) < 1) OR (strlen($_POST['tContent']) < 1)) {
      die('Please fill in all the fields!');
    }

    // Check field constraint
    if (strlen($_POST['tTitle']) > 40) {
      die('Please do not enter more than 40 characters for the title!');
    }
    if (strlen($_POST['tContent']) > 65535) {
      die('Please do not enter more than 65,535 characters for the content!');
    }

    // Strip html tags
    $title = strip_tags($_POST['tTitle']);
    $content = strip_tags($_POST['tContent']);

    // Update database
    $stmt = $conn->prepare('UPDATE thread SET tTitle = ?, tContent = ? WHERE tId = ?');
    $stmt->bind_param("ssi",$title,$content,intval($_GET['tId']));
    $result = $stmt->execute();
    if (!$result) {
      die('Query failed. '.$stmt->error);
    }

    $stmt->free_result();
    $stmt->close();

    // Redirect to edited thread
    // Cannot use header because some html have already been sent
    ?>
    <script type="text/javascript">
      window.location = "viewthread.php?tId=<?php echo $_GET['tId']; ?>";
    </script>
    <?php
    die();

  } elseif (isset($_GET['rId'])) {

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

    // Update database
    $stmt = $conn->prepare('UPDATE reply SET rContent = ? WHERE rId = ?');
    $stmt->bind_param("si",$reply,intval($_GET['rId']));
    $result = $stmt->execute();
    if (!$result) {
      die('Query failed. '.$stmt->error);
    }

    $stmt->free_result();
    $stmt->close();

    // Redirect to edited thread
    // Cannot use header because some html have already been sent
    ?>
    <script type="text/javascript">
      window.location = "viewthread.php?tId=<?php echo $tId; ?>";
    </script>
    <?php
    die();

  }

} else {

  // Display form
  if (isset($_GET['tId'])) {

    // Get thread title and content
    $stmt = $conn->prepare('SELECT tTitle, tContent FROM thread WHERE tId = ?');
    $stmt->bind_param("i",intval($_GET['tId']));
    $result = $stmt->execute();
    if (!$result) {
      die('Query failed. '.$stmt->error);
    }

    $stmt->bind_result($tTitle,$tContent);
    $stmt->fetch();

    ?>

    <div class="row">
    <form class="col s12 m12 l6" action="" method="post">
      <div class="row">
        <div class="input-field col s12">
          <input value="<?php echo $tTitle; ?>" id="tTitle" name="tTitle" type="text" data-length="40">
          <label for="tTitle">Title</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <textarea id="tContent" name="tContent" class="materialize-textarea" data-length="65535"><?php echo $tContent; ?></textarea>
          <label for="tContent">Thread content</label>
        </div>
      </div>
      <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Apply
      <i class="material-icons right">send</i>
    </form>
    </div>
    </div>

    <?php

    $stmt->free_result();
    $stmt->close();

  } elseif (isset($_GET['rId'])) {

    // Get reply content
    $stmt = $conn->prepare('SELECT rContent FROM reply WHERE rId = ?');
    $stmt->bind_param("i",intval($_GET['rId']));
    $result = $stmt->execute();
    if (!$result) {
      die('Query failed. '.$stmt->error);
    }

    $stmt->bind_result($rContent);
    $stmt->fetch();

    ?>

    <div class="row">
      <form class="col s12 m12 l6" action="" method="post">

        <div class="row">
          <div class="input-field col s12">
            <textarea id="reply" name="reply" class="materialize-textarea" data-length="65535"><?php echo $rContent; ?></textarea>
            <label for="reply">Reply</label>
          </div>
        </div>

        <div class="row">
          <div class="col s12">
            <button class="btn waves-effect waves-light purple" type="submit" name="submit" value="submit">Apply
            <i class="material-icons right">send</i></button>
          </div>
        </div>

      </form>
    </div>

    <?php

    $stmt->free_result();
    $stmt->close();

  }

}

?>
