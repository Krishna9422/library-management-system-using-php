<?php
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';

// Initialize variables
$show_otp_form = false;
$show_reset_form = false;
$manager_email = '';

// Step 1: Process Email Submission for OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['manager_email']) && !isset($_POST['otp'])) {
        $manager_email = trim($_POST['manager_email']);

        if (!empty($manager_email)) {
            $conn = new mysqli('localhost', 'root', '', 'librarydb');
            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            // Check if email exists in managers table
            $stmt = $conn->prepare("SELECT id FROM managers WHERE email = ?");
            $stmt->bind_param('s', $manager_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Generate OTP
                $otp = random_int(100000, 999999);

                // Save OTP to password_resets table
                $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE otp = ?, created_at = NOW()");
                $stmt->bind_param('sis', $manager_email, $otp, $otp);
                if ($stmt->execute()) {
                    // Send OTP via email using PHPMailer
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
                        $mail->SMTPAuth = true;
                        $mail->Username = 'Your@mail.com'; // SMTP username
                        $mail->Password = 'password'; // SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587; // or 465 for SSL

                        $mail->setFrom('Your@mail.com', 'Library Management System');
                        $mail->addAddress($manager_email);

                        $mail->isHTML(true);
                        $mail->Subject = 'Your OTP for Password Reset';
                        $mail->Body = "<p>Your OTP for password reset is: <strong>$otp</strong></p><p>This OTP is valid for 10 minutes.</p>";

                        $mail->send();
                        $show_otp_form = true;
                    } catch (Exception $e) {
                        echo "<p>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
                    }
                } else {
                    echo "<p>Failed to save OTP. Try again later.</p>";
                }
            } else {
                echo "<p>No account found with that email address.</p>";
            }

            $stmt->close();
            $conn->close();
        } else {
            echo "<p>Please enter a valid email address.</p>";
        }
    }

    // Step 2: Verify OTP
    if (isset($_POST['verify_otp'])) {
        $otp_entered = trim($_POST['otp']);
        $manager_email = trim($_POST['manager_email']);

        if (!empty($otp_entered) && !empty($manager_email)) {
            $conn = new mysqli('localhost', 'root', '', 'librarydb');
            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            // Verify OTP
            $stmt = $conn->prepare("SELECT otp, created_at FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param('s', $manager_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stored_otp = $row['otp'];
                $created_at = $row['created_at'];
                $otp_expiry_time = 10 * 60;

                $current_time = time();
                $otp_time = strtotime($created_at);
                if (($current_time - $otp_time) <= $otp_expiry_time) {
                    if ($otp_entered == $stored_otp) {
                        $show_reset_form = true;
                    } else {
                        echo "<p>Invalid OTP. Please try again.</p>";
                    }
                } else {
                    echo "<p>OTP has expired. Please request a new one.</p>";
                }
            } else {
                echo "<p>No OTP found for this email. Please try again.</p>";
            }

            $stmt->close();
            $conn->close();
        } else {
            echo "<p>Please enter the OTP.</p>";
        }
    }

    // Step 3: Reset Password
    if (isset($_POST['reset_password'])) {
        $new_password = trim($_POST['new_password']);
        $manager_email = trim($_POST['manager_email']);

        if (!empty($new_password) && !empty($manager_email)) {
            if (strlen($new_password) < 8) {
                echo "<p>Password must be at least 8 characters long.</p>";
            } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/\d/', $new_password) || !preg_match('/[@$!%*?&]/', $new_password)) {
                echo "<p>Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.</p>";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $conn = new mysqli('localhost', 'root', '', 'librarydb');
                if ($conn->connect_error) {
                    die('Connection failed: ' . $conn->connect_error);
                }

                // Update password in managers table
                $stmt = $conn->prepare("UPDATE managers SET password = ? WHERE email = ?");
                $stmt->bind_param('ss', $hashed_password, $manager_email);

                if ($stmt->execute()) {
                    echo "<p>Password has been successfully reset!</p>";
                    $show_otp_form = false;
                    $show_reset_form = false;
                } else {
                    echo "<p>Failed to reset password. Please try again.</p>";
                }

                $stmt->close();
                $conn->close();
            }
        } else {
            echo "<p>Please enter a new password.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Password Reset</title>
</head>
<link rel="stylesheet" href="./style/loan.css">
    <style>
         body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color:#fc25f8;
            color: white;
            padding: 10px 20px;
        }
        .logo {
            height: 50px;
        }
        nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            margin-right: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        nav ul li a:hover {
            text-decoration: underline;
        }
       h2{
            text-align: center;
            color: #4A4A4A;
            margin-top: 20px;}
            button{
                width: 100%;
            padding: 10px;
            background-color: #6200ea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            }
    </style>
<body>
</head>
<body>
    <header>
        <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul class="list">
                <li><a href="http://localhost/library_final/index.html">Home</a></li>
               
            </ul>
        </nav>
    </header>

<h2>Staff Password Reset</h2>

<!-- Step 1: Email form for OTP -->
<?php if (!$show_otp_form && !$show_reset_form): ?>
<form action="" method="POST">
    <label for="manager_email">Enter your email:</label>
    <input type="email" name="manager_email" id="manager_email" required>
    <button type="submit">Send OTP</button>
</form>
<?php endif; ?>

<!-- Step 2: OTP form -->
<?php if ($show_otp_form): ?>
<form action="" method="POST">
    <label for="otp">Enter OTP:</label>
    <input type="text" name="otp" id="otp" required>
    <input type="hidden" name="manager_email" value="<?php echo htmlspecialchars($manager_email); ?>">
    <button type="submit" name="verify_otp">Verify OTP</button>
</form>
<?php endif; ?>

<!-- Step 3: Password reset form -->
<?php if ($show_reset_form): ?>
<form action="" method="POST">
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" id="new_password" required>
    <input type="hidden" name="manager_email" value="<?php echo htmlspecialchars($manager_email); ?>">
    <button type="submit" name="reset_password">Reset Password</button>
</form>
<?php endif; ?>

</body>
</html>
