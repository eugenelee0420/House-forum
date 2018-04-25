<?php
// Script to create forums after first time setup

require "functions.php";

// Die if forum table have records

$sql = 'SELECT * FROM forum';
$result = $conn->query($sql);
if (!$result) {
  die('Query failed. '.$conn->error);
}

if ($conn->affected_rows > 0) {
  die('The forums are already set up, you do not need this script.');
}

if ($_POST['submit'] == "submit") {

} else {

  // Display form

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

  <div class="row"><div class="col s12">
    <h3>First time setup script - Forums setup</h3>
  </div></div>

  <div class="row">
    <form class="col s12 m12 l6" action="" method="post">

      <?php

      // Grab house ID and names
      $sql = 'SELECT * FROM house';
      $result = $conn->query($sql);
      if (!$result) {
        die('Query failed. '.$conn->error);
      }

      // Create house forums
      while ($row = mysqli_fetch_assoc($result)) {

        echo '<div class="row"><div class="col s12">';
        echo '<p>Create forum for house "'.$row['houseName'].'"</p>';
        echo '</div></div>';

        echo '<div class="row"><div class="input-field col s12">';
        echo '<input id="hf_id_'.$row['hId'].'" name="hf_id_'.$row['hId'].'" data-length="3" type="text">';
        echo '<label for="hf_id_'.$row['hId'].'">Forum ID for house "'.$row['houseName'].'"</label>';
        echo '</div></div>';

        echo '<div class="row"><div class="input-field col s12">';
        echo '<input id="hf_name_'.$row['hId'].'" name="hf_name_'.$row['hId'].'" data-length="30" type="text">';
        echo '<label for="hf_name_'.$row['hId'].'">Forum name for house "'.$row['houseName'].'"</label>';
        echo '</div></div>';

        echo '<div class="row"><div class="input-field col s12">';
        echo '<textarea id="hf_des_'.$row['hId'].'" name="hf_des_'.$row['hId'].'" data-length="100" class="materialize-textarea"></textarea>';
        echo '<label for="hf_des_'.$row['hId'].'">Forum description for house "'.$row['houseName'].'" (Can be left blank)</label>';
        echo '</div></div>';

        echo '<br><br>';

      }

      ?>

    </form>
  </div>

  <?php

}

?>
