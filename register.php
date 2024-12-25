<?php
session_start();
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';

// Include PHPMailer files (adjust path as needed)
 // If using Composer, otherwise manually include PHPMailer files

 
 if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
     // Server-side validation
 
     // Validate Name (no symbols or numbers)
     if (!preg_match("/^[A-Za-z ]+$/", $_POST['name'])) {
         die("Name should not contain any symbols or numbers.");
     }
 
     // Validate Email (should end with @mgmcen.ac.in)
     $email = $_POST['email'];
     if (!preg_match("/^[a-zA-Z0-9._%+-]+@mgmcen\.ac\.in$/", $email)) {
         die("Email should end with @mgmcen.ac.in.");
     }
 
     // Validate Password (at least 1 capital letter and 1 symbol)
     $password = $_POST['password'];
     if (!preg_match("/^(?=.*[A-Z])(?=.*[\W_]).+$/", $password)) {
         die("Password should contain at least one capital letter and one symbol.");
     }
 
     // Hash the password for secure storage
     $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
 
     // Generate OTP
     $otp = rand(100000, 999999);
     $_SESSION['otp'] = $otp;
     $_SESSION['name'] = $_POST['name'];
     $_SESSION['email'] = $email;
     $_SESSION['password'] = $hashedPassword;
 
     // Send OTP via Email using PHPMailer
     $mail = new PHPMailer(true);
 
     try {
         // Server settings
         $mail->isSMTP();
         $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
         $mail->SMTPAuth = true;
         $mail->Username = 'Your@mail.com'; // SMTP username
         $mail->Password = 'app password'; // SMTP password
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
         $mail->Port = 587; // or 465 for SSL
 
         // Recipients
         $mail->setFrom('Your@mail.com', 'Your Name');
         $mail->addAddress($email, $_POST['name']); // Add a recipient
 
         // Content
         $mail->isHTML(true);
         $mail->Subject = 'Your OTP for Registration';
         $mail->Body    = "Hello {$_POST['name']},<br>Your OTP is: <strong>$otp</strong>";
 
         // Send email
         $mail->send();
         echo "OTP sent to your email. Please verify.";
         header('Location: verify.php'); // Redirect to OTP verification page
         exit();
     } catch (Exception $e) {
         echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
     }
 }
 ?>
 