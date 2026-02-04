<?php
include 'sendEmail.php';

sendEmail('your_own_email@gmail.com', 'Test Mail', 'This is a test email from PHPMailer!');
echo "Mail sent test completed!";
?>
