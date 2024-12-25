<?php
require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php'; 

$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'Your@mail.com'; // Your Gmail address
$mail->Password = 'app password'; // Your Gmail app password
PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
$mail->Port = 587;

// Enable verbose debug output
$mail->SMTPDebug = 2;

$mail->setFrom('Your@mail.com', 'krishna');
$mail->addAddress('Your@mail.com', 'gandhewar');
$mail->Subject = 'Test Email';
$mail->Body    = 'This is a test email.';

if($mail->send()) {
    echo 'Email has been sent successfully!';
} else {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}
?>