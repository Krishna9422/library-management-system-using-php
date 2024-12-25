<?php
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Send OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_otp'])) {
    $email = $_POST['email'];
    $old_password = $_POST['old_password'];

    // Verify email and old password
    $query = "SELECT * FROM managers WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $old_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $otp_expiry = time() + 300; // OTP valid for 5 minutes

        // Save OTP and expiry to the database
        $otp_query = "UPDATE managers SET otp = ?, otp_expiry = ? WHERE email = ?";
        $otp_stmt = $conn->prepare($otp_query);
        $otp_stmt->bind_param("iis", $otp, $otp_expiry, $email);
        $otp_stmt->execute();

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server (e.g., Gmail, SendGrid)
            $mail->SMTPAuth = true;
            $mail->Username = 'Your@mail.com'; // SMTP username
            $mail->Password = 'app password'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;


            $mail->setFrom('Your@mail.com', 'Your Website');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'OTP for Password Change';
            $mail->Body    = "Your OTP is: <b>$otp</b>. It is valid for 5 minutes.";

            $mail->send();
            echo "<p>OTP has been sent to your email.</p>";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<p>Invalid email or old password.</p>";
    }
}

// Step 2: Verify OTP and Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];

    // Verify OTP
    $query = "SELECT * FROM managers WHERE otp = ? AND otp_expiry >= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $otp, time());
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Update password
        $update_query = "UPDATE managers SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ss", $new_password, $user['email']);
        $update_stmt->execute();

        echo "<p>Password changed successfully!</p>";
    } else {
        echo "<p>Invalid or expired OTP.</p>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .form-section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h2>Request OTP</h2>
            <form method="POST" action="">
                <label for="email">Email:</label>
                <input type="email" name="email" placeholder="Enter your email" required>

                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" placeholder="Enter your old password" required>

                <button type="submit" name="send_otp">Send OTP</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Change Password</h2>
            <form method="POST" action="">
                <label for="otp">OTP:</label>
                <input type="text" name="otp" placeholder="Enter the OTP" required>

                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" placeholder="Enter new password" required>

                <button type="submit" name="change_password">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
