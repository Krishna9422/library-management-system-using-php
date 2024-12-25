<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';

include 'db.php';

$success = false;
$otpVerified = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (isset($_POST['verify_otp'])) {
        $userOtp = $_POST['otp'];
        if ($userOtp == $_SESSION['otp']) {
            $otpVerified = true;

            // Hash the password
            $hashedPassword = password_hash($_SESSION['password'], PASSWORD_DEFAULT);

            // Insert the new manager into the database
            $insertQuery = "INSERT INTO managers (name, email, password) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("sss", $_SESSION['name'], $_SESSION['email'], $hashedPassword);

            if ($insertStmt->execute()) {
                $success = true;
            } else {
                $error = "There was an error creating your account. Please try again.";
            }
            unset($_SESSION['otp'], $_SESSION['name'], $_SESSION['email'], $_SESSION['password']);
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    } else {
        // Validation checks for name, email, and password
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
                    $otpSent = true;
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
    <title>Manager Signup with OTP</title>
    <link rel="stylesheet" href="./style/return.css">
    <style>
        /* Existing styles */
        h2 {
            text-align: center;
            color: #4A4A4A;
            font-size: 24px;
            margin-top: 20px;
        }
        /* Form Styles */
        form {
            width: 100%;
            max-width: 400px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        form input[type="email"],
        form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: violet;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form input[type="submit"]:hover {
            background-color: #5f3db5;
        }
        /* Error message styles */
        p.error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
        /* Password Strength Indicator */
        .strength-bar {
            height: 5px;
            width: 100%;
            margin-top: 5px;
            background-color: #e0e0e0;
        }
        .strength-bar div {
            height: 100%;
            width: 0%;
            background-color: red;
        }
        .strength-text {
            font-size: 14px;
            text-align: center;
        }
    </style>

<body>
<header>
    <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    <nav>
        <ul>
            <li><a href="./index.html">Home</a></li>
            <li><a href="book.php">Books</a></li>
        </ul>
    </nav>
</header>

<h2>Staff Signup</h2>
<?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

<?php if (!isset($_POST['verify_otp']) && isset($otpSent) && $otpSent): ?>
    <form action="" method="POST" id="otp-form">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" id="otp" required>
        <input type="hidden" name="verify_otp" value="1">
        <input type="submit" value="Verify OTP">
    </form>
<?php elseif (!$otpVerified): ?>
    <form action="" method="POST" id="signup-form">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required pattern="^[A-Za-z\s]+$" title="Name should only contain alphabets.">

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required pattern="[a-zA-Z0-9._%+-]+@mgmcen\.ac\.in$" 
               title="Only '@mgmcen.ac.in' emails are allowed.">

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required minlength="8" 
               onkeyup="checkPasswordStrength()">

        <div id="strength-bar">
            <div></div>
        </div>
        <p id="strength-text">Password strength: Weak</p>

        <input type="submit" value="Send OTP">
    </form>
<?php else: ?>
    <p>Signup successful! You can now <a href="manager-login.php">log in</a>.</p>
<?php endif; ?>

<script>
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('strength-bar').firstElementChild;
    const strengthText = document.getElementById('strength-text');
    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[!@#$%^&*(),.?\":{}|<>]/.test(password)) strength++;

    const colors = ['red', 'orange', 'yellow', 'green'];
    const strengths = ['Weak', 'Fair', 'Good', 'Strong'];

    strengthBar.style.width = (strength * 25) + '%';
    strengthBar.style.background = colors[strength - 1] || 'red';
    strengthText.textContent = 'Password strength: ' + (strengths[strength - 1] || 'Weak');
}
</script>
</body>
</html>
