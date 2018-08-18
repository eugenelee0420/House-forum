<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = $cfg['mailHost'];
$mail->Port = intval($cfg['mailPort']);
$mail->SMTPAuth = true;
$mail->Username = $cfg['mailUser'];
$mail->Password = $cfg['mailPass'];
$mail->setFrom('noreply@'.$_SERVER['HTTP_HOST']);

?>
