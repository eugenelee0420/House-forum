<?php
// login page

require 'functions.php';

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
if ($_SESSION['logged_in'] == 1) {
    header('Location: index.php');
    die();
}

// Check if form is submitted
if ($_POST['submit'] == 'submit') {

  // Login Check
    $stmt = $conn->prepare('SELECT studentId, hash FROM users WHERE studentId = ? ');
    $stmt->bind_param('s', $_POST['studentId']);
    $result = $stmt->execute();

    // Check query result
    if (!$result) {
        die('Query failed. '.$conn->error);
    }

    $stmt->bind_result($qStudentId, $qHash);
    $stmt->fetch();
    $stmt->free_result();
    $stmt->close();

    // Check if the inputted studentId is correct / exists
    if ($qStudentId !== $_POST['studentId']) {
        die('Student ID or password is incorrect!');
    }

    // Check password
    if (!password_verify($_POST['pass'], $qHash)) {
        die('Student ID or password is incorrect!');
    }

    // Check if user is using tfa
    $stmt = $conn->prepare('SELECT studentId, tfaSecret FROM tfa WHERE studentId = ?');
    $stmt->bind_param('s', $_POST['studentId']);
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->bind_result($qStudentId, $tfaSecret);
    $stmt->fetch();
    $stmt->free_result();
    $stmt->close();

    if ($qStudentId == $_POST['studentId']) {

    // Using tfa
        // Check if otp field is filled
        if (strlen($_POST['otp'] < 1)) {
            die('2-factor authentication is enabled, please provide one time token to login.');
        }

        // Verify code
        $result = $tfa->verifyCode($tfaSecret, $_POST['otp']);
        if (!$result) {
            die('OTP verification failed');
        }
    }

    // Login success
    // regenerate session id on Login
    session_regenerate_id();

    // Update database (update before setting variable. If update failed (maybe duplicate session id), user need to retry and generate new session id)
    $stmt = $conn->prepare('INSERT INTO session (sessionId, studentId, lastActivity) VALUES ("'.session_id().'", ?, '.time().');');
    $stmt->bind_param('s', $_POST['studentId']);
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed! Please retry. '.$stmt->error);
    }
    // Set session variable to indicate logged in
    $_SESSION['logged_in'] = 1;

    // Add login record
    $stmt = $conn->prepare('INSERT INTO loginRecord (time, studentId, ip) VALUES ('.time().',?,"'.getIp().'")');
    $stmt->bind_param('s', $_POST['studentId']);
    $result = $stmt->execute();
    if (!$result) {
        die('Query failed. '.$stmt->error);
    }

    $stmt->free_result();
    $stmt->close();

    header('Location: index.php');
    die();

// If form was not submitted, display the form (html)
} else {
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

<nav>
  <div class="nav-wrapper purple lighten-2">
		<div class="row">
			<div class="col s12">
    		<a href="index.php" class="brand-logo center flow-text">House Forums</a>
			</div>
		</div>
  </div>
</nav>

<!-- Login form -->
<div class="row">
  <form class="col s12 m12 l6" method="post" action="">

    <div class="row">
      <div class="input-field col s12">
        <input id="studentId" name="studentId" type="text">
        <label for="studentId">Student ID</label>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12">
        <input id="pass" name="pass" type="password">
        <label for="pass">Password</label>
      </div>
    </div>

    <div class="row">
      <div class="input-field col s12">
        <input id="otp" name="otp" type="password" data-length="6">
        <label for="otp">One time password (enter if enabled)</label>
      </div>
    </div>

    <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Submit
    <i class="material-icons right">send</i>
    </button>

  </form>
</div>


</body>
</html>

<?php
// Close if statement
}
?>
