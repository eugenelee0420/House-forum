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

  // Get the houses
  $sql = 'SELECT * FROM house';
  $result = $conn->query($sql);
  if (!$result) {
    die('Query failed. '.$conn->error);
  }

  // Convert query result into array
  $houses = array();
  $count = 0;
  while ($row = mysqli_fetch_assoc($result)) {

    $houses[$count]['id'] = $row['hId'];
    $houses[$count]['name'] = $row['houseName'];

    $count++;

  }

  // Check if all required fields are filled in
  $errormsg = 'Please fill in all the required fields!';
  foreach ($houses as $row) {

    if (strlen($_POST['hf_id_'.$row['id']]) < 1) {
      die($errormsg);
    }

    if (strlen($_POST['hf_name_'.$row['id']]) < 1) {
      die($errormsg);
    }

  }

  if ((strlen($_POST['ihf_id']) < 1) OR (strlen($_POST['ihf_name']) < 1)) {
    die($errormsg);
  }

  // Check field constraints
  foreach ($houses as $row) {

    if (strlen($_POST['hf_id_'.$row['id']]) > 3) {
      die('Please do not input more than 3 characters for the forum ID! ('.$row['name'].')');
    }

    if (strlen($_POST['hf_name_'.$row['id']]) > 30) {
      die('Please do not input more than 30 characters for the forum name! ('.$row['name'].')');
    }

    if (strlen($_POST['hf_des_'.$row['id']]) > 100) {
      die('Please do not input more than 100 characters for the forum description! ('.$row['name'].')');
    }

  }

  if (strlen($_POST['ihf_id']) > 3) {
    die('Please do not input more than 3 characters for the forum ID! (Inter-house forum)');
  }

  if (strlen($_POST['ihf_name']) > 30) {
    die('Please do not input more than 30 characters for the forum name! (Inter-house forum)');
  }

  if (strlen($_POST['ihf_des']) > 100) {
    die('Please do not input more than 100 characters for the forum description! (Inter-house forum)');
  }

  // Insert records
  $stmt = $conn->prepare('INSERT INTO forum (fId, fName, fDescription, hId) VALUES (?,?,?,?)');

  foreach ($houses as $row) {

    $stmt->bind_param("ssss",$_POST['hf_id_'.$row['id']],$_POST['hf_name_'.$row['id']],$_POST['hf_des_'.$row['id']],$row['id']);
    $result = $stmt->execute();
    if (!$result) {
      die('Query failed. '.$stmt->error);
    }

    $stmt->free_result();

  }

  $stmt = $conn->prepare('INSERT INTO forum (fId, fName, fDescription, hId) VALUES (?,?,?,NULL)');
  $stmt->bind_param("sss",$_POST['ihf_id'],$_POST['ihf_name'],$_POST['ihf_des']);
  $result = $stmt->execute();
  if (!$result) {
    die('Query failed. '.$stmt->error);
  }

  $stmt->free_result();

  echo 'The forums are set up. You can view them <a href="index.php">here.</a>';

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

      mysqli_free_result($result);

      ?>

      <div class="row">
        <div class="col s12">
          <p>Create an inter-house forum</p>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="ihf_id" name="ihf_id" type="text" data-length="3">
          <label for="ihf_id">Inter-house forum ID</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input id="ihf_name" name="ihf_name" type="text" data-length="30">
          <label for="ihf_des">Inter-house forum Name</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <textarea id="ihf_des" name="ihf_des" data-length="100" class="materialize-textarea"></textarea>
          <label for="ihf_des">Inter-house forum Description (Can be left blank)</label>
        </div>
      </div>


      <button class="btn waves-effect purple waves-light" type="submit" name="submit" value="submit">Submit
      <i class="material-icons right">send</i>
    </form>
  </div>

  <?php

}

?>
