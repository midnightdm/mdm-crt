<?php
include_once('classes/Messages.class.php');

$mail = new Messages();
$message = [['to'=> 'bgtalkingdog@gmail.com', 'subject'=> 'test 2', 'text'=>'This is a second sample email.']];
$mail->sendEmail($message);
echo "Test email sent.";

?>