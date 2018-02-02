<?php
// Setup script for first time use
if ($_POST['submit'] == "submit") {
  // Form submitted

  // Echo submitted data
  echo 'Submitted data (PHP array):';
  echo '<pre>';
  var_dump($_POST);
  echo '</pre><br>';

  // Check if all fields are filled in
  echo 'Checking if all the fields are filled in...<br>';

  if (strlen($_POST['dbHost']) < 1) {
    die('Error: Database hostname field is empty!');
  }

  if (strlen($_POST['dbUser']) < 1) {
    die('Error: Database user field is empty!');
  }

  if (strlen($_POST['dbPass']) < 1) {
    die('Error: Database password field is empty!');
  }

  if (strlen($_POST['dbName']) < 1) {
    die('Error: Database name field is empty!');
  }

  if (strlen($_POST['rowsPerPage']) < 1) {
    die('Error: Rows per page field is empty!');
  }

  if (strlen($_POST['avatarPic']) < 1) {
    die('Error: Avatar picture field is empty!');
  }

  if (strlen($_POST['bgPic']) < 1) {
    die('Error: Background picture field is empty!');
  }

  if (strlen($_POST['userTimeout']) < 1) {
    die('Error: User timeout field is empty!');
  }

  if (strlen($_POST['timezoneOffset']) < 1) {
    die('Error: Timezone offset field is empty!');
  }

  if (strlen($_POST['welcomeMsg']) < 1) {
    die('Error: Welcome message field is empty!');
  }

  if (strlen($_POST['house']) < 1) {
    die('Error: House field is empty!');
  }

  if (strlen($_POST['userGroup']) < 1) {
    die('Error: User group ID field is empty!');
  }

  if (strlen($_POST['userGroupName']) < 1) {
    die('Error: User group name field is empty!');
  }

  if (strlen($_POST['studentId']) < 1) {
    die('Error: Student ID field is empty!');
  }

  if (strlen($_POST['pass']) < 1) {
    die('Error: User password field is empty!');
  }

  if (strlen($_POST['userHId']) < 1) {
    die('Error: User house ID field is empty!');
  }

  echo 'No error was found<br><br>';

  // Check JSON
  echo 'Decoding and validating JSON...<br>';

  $json = json_decode($_POST['house'], true);
  if ($json == NULL) {
    die('Error: Invalid JSON');
  } else {
    echo 'No error was found<br>';
  }

  echo 'Decoded JSON (PHP array):<br><pre>';
  var_dump($json);
  echo '</pre><br><br>';

  // Check field constraints
  echo 'Checking field constraints...<br>';

  if (intval($_POST['rowsPerPage']) < 1) {
    die('Error: Invalid value for rows per page!');
  }

  if (strlen($_POST['avatarPic']) > 200) {
    die('Error: Avatar picture field exceeds field limit of 200 characters!');
  }

  if (strlen($_POST['bgPic']) > 200) {
    die('Error: Background picture field exceeds field limit of 200 characters!');
  }

  if (intval($_POST['userTimeout']) < 1) {
    die('Error: Invalid value for user timeout!');
  }

  if (strlen($_POST['welcomeMsg']) > 65535) {
    die('Error: Welcome message field exceeds field limit of 65535 characters!');
  }

  if (strlen($_POST['userGroup']) > 3) {
    die('Error: User group ID field exceeds field limit of 3 characters!');
  }

  if (strlen($_POST['userGroupName']) > 50) {
    die('Error: User group name field exceeds field limit of 50 characters!');
  }

  if (strlen($_POST['studentId']) > 7) {
    die('Error: Student ID field exceeds field limit of 7 characters!');
  }

  if (strlen($_POST['userHId']) > 3) {
    die('User house ID field exceeds field limit of 3 characters!');
  }

  // Prepare empty array for checking for duplicate value within input data
  $hIdArray = array();

  // Check field constraints within JSON array
  foreach($json as $key => $row) {

    // Append hId to array
    array_push($hIdArray,$row['hId']);

    if (strlen($row['hId']) < 1) {
      die('Error: House ID parameter is empty or not correctly specified for entry number '.$key);
    }

    if (strlen($row['houseName']) < 1) {
      die('Error: House name parameter is empty or not correctly specified for entry number '.$key.' (house ID: '.$row['hId'].')');
    }

    if (strlen($row['hId']) > 3) {
      die('Error: House ID parameter exceeds field limit of 3 characters for entry number '.$key.' (house ID: '.$row['hId'].')');
    }

    if (strlen($row['houseName']) > 20) {
      die('Error: House name parameter exceeds field limit of 20 characters for entry number '.$key.' (house ID: '.$row['hId'].')');
    }

  }

  echo 'No error was found<br><br>';

  var_dump($hIdArray);


} else {

  // Form not submitted, display form (html)

  ?>

  <!DOCTYPE html>
  <html>
  <head>
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--Import materialize.css-->
  <!-- Compiled and minified CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.1/css/materialize.min.css">

  <!-- Highlight.js -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/atom-one-dark.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
  <script>hljs.initHighlightingOnLoad();</script>

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

  <div class="row"><div class="col s12">
    <h3>First time setup script</h3>
  </div></div>

  <div class="row">
    <form class="col s12 m12 l6" action="" method="post">

      <h4>Database credentials</h4>
      <p>These settings can be changed later in cfg.json</p>

      <div class="row">
        <div class="input-field col s12">
          <input id="dbHost" name="dbHost" type="text">
          <label for="dbHost">Database hostname</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="dbUser" name="dbUser" type="text">
          <label for="dbUser">Database username</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="dbPass" name="dbPass" type="password">
          <label for="dbPass">Database password</label>
        </div>
      </div>

      <div class="row">
        <div class="col s12">
          <p>Input a database name that does not already exist. A new database will be created.</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="dbName" name="dbName" type="text">
          <label for="dbName">Database name</label>
        </div>
      </div>

      <h4>User settings dafaults</h4>
      <p>These setting can be changed later by the users on the user settings page.</p>

      <div class="row">
        <div class="col s12">
          <p>Number of threads displayed per thread listing page (recommended: 10):</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="rowsPerPage" name="rowsPerPage" type="number">
          <label for="rowsPerPage">Rows per page</label>
        </div>
      </div>

      <div class="row">
        <div class="col s12">
          <p>Avatar image (link to external image or local relative link)(must be in 1:1 aspect ratio):</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="avatarPic" name="avatarPic" type="text" data-length="200">
          <label for="avatarPic">Avatar picture</label>
        </div>
      </div>

      <div class="row">
        <div class="col s12">
          <p>Background picture (link to external image or local relative link):</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="bgPic" name="bgPic" type="text" data-length="200">
          <label for="bgPic">Background picture</label>
        </div>
      </div>

      <h4>Global settings</h4>
      <p>These settings can be changed later at the global settings page.</p>

      <div class="row">
        <div class="col s12">
          <p>Idle time before user is logged out automatically (in seconds):</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="userTimeout" name="userTimeout" type="number">
          <label for="userTimeout">User timeout</label>
        </div>
      </div>

      <div class="row">
        <div class="col s12">
          <p>UNIX epoch timezome offset. Refer to <a href="https://www.epochconverter.com/timezones" target="_blank">this website</a> for more details.</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="timezoneOffset" name="timezoneOffset" type="number">
          <label for="timezoneOffset">Timezone offset</label>
        </div>
      </div>

      <div class="row">
        <div class="col s12">
          <p>Welcome message displayed on landing page (index.php):</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <textarea id="welcomeMsg" name="welcomeMsg" class="materialize-textarea" data-length="65535"></textarea>
          <label for="welcomeMsg">Welcome message</label>
        </div>
      </div>

      <!-- Create houses
      Create new user (ultimate user group) -->

      <h4>Houses</h4>
      <p>These should not be changed later. New houses can be added directly to the database.</p>

      <div class="row">
        <div class="col s12">
          <p>These are the houses which the users belong to. Each user must belong to one house.</p>
          <p>Please use the JSON format to input the data. An example is shown below.</p>
          <p>hId is an unique identifyer of the houses, while houseName is the displayed name of the house.</p>
          <p>hId should be at most 3 characters long, houseName should be at most 20 characters long.</p>
        </div>
      </div>

      <div class="row"><div class="col s12">
        <p>Sample of the JSON data:</p>
      <pre><code class="json">[
  {
    "hId":"Y",
    "houseName":"Yellow"
  },
  {
    "hId":"R",
    "houseName":"Red"
  }
]</code></pre>
      </div></div>

      <div class="row">
        <div class="input-field col s12">
          <textarea id="house" name="house" class="materialize-textarea"></textarea>
          <label for="house">Houses</label>
        </div>
      </div>

      <h4>User group</h4>
      <p>Create an "ultimate" user group that have every permission. Other user groups can be created later at the user group settings page.</p>

      <div class="row">
        <div class="col s12">
          <p>Unique ID of the user group:</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="userGroup" name="userGroup" type="text" data-length="3">
          <label for="userGroup">User group ID</label>
        </div>
      </div>

      <div class="row">
        <div class="col s12">
          <p>Name of the user group:</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="userGroupName" name="userGroupName" type="text" data-length="50">
          <label for="userGroupName">User group name</label>
        </div>
      </div>

      <h4>User</h4>
      <p>Create a user for setting up rest of the forum. New users should be added using the add_user.php script.</p>
      <p>This user will belong to the "ultimate" user group created above.</p>

      <div class="row">
        <div class="col s12">
          <p>A unique ID for each user, used to login to the forum.</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="studentId" name="studentId" type="text" data-length="7">
          <label for="studentId">Student ID</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="pass" name="pass" type="password">
          <label for="pass">User password</label>
        </div>
      </div>

      <div class="row">
        <div class="col s12">
          <p>The house ID of the user:</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="userHId" name="userHId" type="text" data-length="3">
          <label for="userHId">House ID (user)</label>
        </div>
      </div>

      <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Submit
      <i class="material-icons right">send</i>


  </form>
</div>


  <?php


}


?>
