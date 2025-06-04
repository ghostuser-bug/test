<?php
require 'mailer.php';
if(sendMail('syedraziq265@gmail.com', 'Test Email', 'This is a test email from PHPMailer.')) {
    echo "Sent!";
} else {
    echo "Failed!";
}
?>