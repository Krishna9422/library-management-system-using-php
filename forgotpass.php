<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php'; // Include PHPMailer's autoloader if using Composer

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "librarydb"; // Ensure PHPMailer is installed via Composer

// Initialization of variables
$show_otp_form = false;
$show_reset_form = false;
$borrower_email = '';
$otp_entered = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['borrower_email']) && !isset($_POST['otp'])) {
        // Step 1: Email form submission for OTP
        $borrower_email = trim($_POST['borrower_email']);

        if (!empty($borrower_email)) {
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'librarydb');

            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM borrowers WHERE borrower_email = ?");
            $stmt->bind_param('s', $borrower_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Generate a unique OTP
                $otp = random_int(100000, 999999);

                // Save OTP to the database
                $stmt = $conn->prepare("INSERT INTO password_resets (email, otp, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE otp = ?, created_at = NOW()");
                $stmt->bind_param('sis', $borrower_email, $otp, $otp);
                if ($stmt->execute()) {
                    // Send email with OTP using PHPMailer
                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // SMTP host (e.g., Gmail, SendGrid)
                        $mail->SMTPAuth = true;
                        $mail->Username = 'Your@mail.com'; // SMTP username
                        $mail->Password = 'password'; // SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        // Recipients
                        $mail->setFrom('Your@mail.com', 'Library Management System');
                        $mail->addAddress($borrower_email);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Password Reset OTP';
                        $mail->Body    = "<p>Your OTP for password reset is:</p><h2>$otp</h2><p>This OTP is valid for 10 minutes.</p>";

                        $mail->send();
                        echo "<p>An OTP has been sent to your email address. Please enter it below.</p>";

                        // Set flag to show OTP form
                        $show_otp_form = true;
                    } catch (Exception $e) {
                        echo "<p>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
                    }
                } else {
                    echo "<p>Failed to save the OTP. Try again later.</p>";
                }
            } else {
                echo "<p>No account found with that email address.</p>";
            }

            $stmt->close();
            $conn->close();
        } else {
            echo "<p>Please enter your email address.</p>";
        }
    }

    if (isset($_POST['verify_otp'])) {
        // Step 2: OTP form submission to verify OTP
        $otp_entered = trim($_POST['otp']);
        $borrower_email = trim($_POST['borrower_email']);

        if (!empty($otp_entered) && !empty($borrower_email)) {
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'librarydb');

            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            // Verify OTP
            $stmt = $conn->prepare("SELECT otp, created_at FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param('s', $borrower_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stored_otp = $row['otp'];
                $created_at = $row['created_at'];
                $otp_expiry_time = 10 * 60; // 10 minutes in seconds

                // Check OTP expiry (10 minutes)
                $current_time = time();
                $otp_time = strtotime($created_at);
                if (($current_time - $otp_time) <= $otp_expiry_time) {
                    // OTP is valid, verify the entered OTP
                    if ($otp_entered == $stored_otp) {
                        echo "<p>OTP verified successfully! You can now reset your password.</p>";

                        // Set flag to show password reset form
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
            echo "<p>Please enter a valid OTP.</p>";
        }
    }

    if (isset($_POST['reset_password'])) {
        // Step 3: Password reset form submission
        $new_password = trim($_POST['new_password']);
        $borrower_email = trim($_POST['borrower_email']);

        if (!empty($new_password) && !empty($borrower_email)) {
            // Server-side validation for password
            if (strlen($new_password) < 8) {
                echo "<p>Password must be at least 8 characters long.</p>";
            } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/\d/', $new_password) || !preg_match('/[@$!%*?&]/', $new_password)) {
                echo "<p>Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.</p>";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Database connection
                $conn = new mysqli('localhost', 'root', '', 'librarydb');

                if ($conn->connect_error) {
                    die('Connection failed: ' . $conn->connect_error);
                }

                // Update the password in the database
                $stmt = $conn->prepare("UPDATE borrowers SET password = ? WHERE borrower_email = ?");
                $stmt->bind_param('ss', $hashed_password, $borrower_email);

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
    <title>Forgot Password</title>
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
    <script>
        function validatePassword() {
            var password = document.getElementById('new_password').value;
            var errorMessage = document.getElementById('password_error');
            
            // Check password length
            if (password.length < 8) {
                errorMessage.textContent = "Password must be at least 8 characters long.";
                return false;
            }

            // Check if password contains at least one uppercase letter, one lowercase letter, one number, and one special character
            var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!regex.test(password)) {
                errorMessage.textContent = "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
                return false;
            }
            
            // Clear error message if the password is valid
            errorMessage.textContent = "";
            return true;
        }
    </script>
</head>
<body>
<header>
        <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul>
                <li><a href="http://localhost/library_final/index.html">Home</a></li>
             
            </ul>
        </nav>
    </header>

    <div class="form-container">
        <h2>Forgot Password</h2>

        <!-- Email form -->
        <?php if (!$show_otp_form && !$show_reset_form) { ?>
            <form method="POST" action="">
                <label for="borrower_email">Enter your email:</label>
                <input type="email" id="borrower_email" name="borrower_email" required placeholder="Your email address">
                <button type="submit">Submit</button>
            </form>
        <?php } ?>

        <!-- OTP form -->
        <?php if ($show_otp_form) { ?>
            <form method="POST" action="">
                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" required placeholder="Enter OTP">
                <input type="hidden" name="borrower_email" value="<?php echo $borrower_email; ?>">
                <button type="submit" name="verify_otp">Verify OTP</button>
            </form>
        <?php } ?>

        <!-- Password reset form -->
        <?php if ($show_reset_form) { ?>
            <form method="POST" action="" onsubmit="return validatePassword()">
                <label for="new_password">Enter New Password:</label>
                <input type="password" id="new_password" name="new_password" required placeholder="New password">
                <div id="password_error" style="color: red;"></div> <!-- For displaying validation errors -->
                <input type="hidden" name="borrower_email" value="<?php echo $borrower_email; ?>">
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>
