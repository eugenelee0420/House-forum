<?php
// Setup script for first time use
if (isset($_POST['submit'])) {
  // Form submitted

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
          <input id="rowsPerPage" name="rowsPerpage" type="text">
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




  <?php


}


?>
