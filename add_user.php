<?php
require "cfg.php";
$conn = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
if ($conn->connect_error) {
	die('<font color="red">Connection failed: '.$conn->connect_error.'</font>');
}
$pass = "eugenelee";
$hash = password_hash($pass, PASSWORD_DEFAULT);
echo $hash;

$sql = 'INSERT INTO users VALUES ("s060063", "eugene", "J", "A", "'.$hash.'")';
$result = $conn->query($sql);
echo "<br>";
var_dump($result);

?>
