<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';
include 'db.php';

$error = ""; // Initialize error message
$success = ""; // Initialize success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle OTP verification
    if (isset($_POST['otp'])) {
        $enteredOtp = trim($_POST['otp']);
        if ($enteredOtp == $_SESSION['otp']) {
            // Insert into database
            $name = $_SESSION['name'];
            $email = $_SESSION['email'];
            $password = password_hash($_SESSION['password'], PASSWORD_BCRYPT); // Hash password for security

            $insertQuery = "INSERT INTO managers (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                $success = "Signup successful!";
                unset($_SESSION['otp'], $_SESSION['name'], $_SESSION['email'], $_SESSION['password']); // Clear session
            } else {
                $error = "Failed to add user to the database.";
            }
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }

    // Handle signup details submission
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validation
        if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
            $error = "Name should only contain alphabets.";
        } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@mgmcen\.ac\.in$/", $email)) {
            $error = "Only '@mgmcen.ac.in' emails are allowed.";
        } elseif (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
            $error = "Password must be at least 8 characters long, contain at least one uppercase letter, and one special character.";
        } else {
            // Check if email exists
            $checkEmailQuery = "SELECT * FROM managers WHERE email = ?";
            $stmt = $conn->prepare($checkEmailQuery);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email already exists. Please use a different email.";
            } else {
                // Generate OTP
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;

                // Send OTP via email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'Your@mail.com'; // Your email
                    $mail->Password = 'password'; // Your email password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('Your@mail.com', 'Library Management');
                    $mail->addAddress($email, $name);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP for Signup';
                    $mail->Body = "<p>Your OTP for signup is: <strong>$otp</strong></p>";

                    $mail->send();
                    $success = "OTP has been sent to your email. Please enter it below.";
                } catch (Exception $e) {
                    $error = "OTP could not be sent. Error: {$mail->ErrorInfo}";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .signup-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .signup-container h2 {
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }
        .signup-container input[type="text"],
        .signup-container input[type="email"],
        .signup-container input[type="password"],
        .signup-container input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .signup-container button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .signup-container button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
        <?php if (!empty($success)) { echo "<p class='success'>$success</p>"; } ?>

        <?php if (empty($_SESSION['otp'])) { ?>
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Enter your name" required>
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Generate OTP</button>
            </form>
        <?php } else { ?>
            <form method="POST" action="">
                <input type="number" name="otp" placeholder="Enter OTP" required>
                <button type="submit">Verify OTP</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>
