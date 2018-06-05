<?php


require 'functions.php';

echo 'userTimeout: '.$userTimeout;
echo '<br>';
echo 'Time: '.time();
echo '<br>';

$sql = 'DELETE FROM session WHERE lastActivity < '.(time() - $userTimeout).';';
$result = $conn->query($sql);
if (!$result) {
    die('Query failed. '.$conn->error);
}

echo 'Rows deleted: '.mysqli_affected_rows($conn);
