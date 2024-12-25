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
$dbname = "librarydb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the name, email, and password from the form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Retrieve the password

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Generate a random OTP for email verification
    $otp = rand(100000, 999999);  // 6-digit OTP

    // Insert the user into the verification_requests table
    $stmt = $conn->prepare("INSERT INTO verification_requests (name, email, password, otp, otp_verified) VALUES (?, ?, ?, ?, ?)");
    $otp_verified = false;
    $stmt->bind_param("sssii", $name, $email, $hashed_password, $otp, $otp_verified);
    $stmt->execute();

    // Send OTP email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // SMTP server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // SMTP host (e.g., Gmail, SendGrid)
        $mail->SMTPAuth = true;
        $mail->Username = 'Your@mail.com'; // SMTP username
        $mail->Password = 'app password'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('Your@mail.com', 'Library System');
        $mail->addAddress($email, $name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for email verification';
        $mail->Body    = "Hello $name,<br><br>Your OTP for email verification is: <strong>$otp</strong>";

        $mail->send();
        echo '<p class="success">OTP has been sent to your email.</p>';
        header('Location: otpadmin.php');
    } catch (Exception $e) {
        echo "<p class='error'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register with OTP</title>
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
        form {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        form input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            width: 100%;
            padding: 10px;
            background-color: #6200ea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #3700b3;
        }
        h2{
            text-align: center;
            color: #4A4A4A;
            margin-top: 20px;}

        .success {
            color: green;
            text-align: center;
            font-weight: bold;
        }
        .error {
            color: red;
            text-align: center;
            font-weight: bold;
        }
        #password-strength {
            height: 5px;
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 3px;
        }
        #password-strength-bar {
            height: 100%;
            width: 0;
            background-color: red;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <header>
        <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul>
                <li><a href="http://localhost/library_final/index.html">Home</a></li>
                <li><a href="http://localhost/library_final/book.php">Books</a></li>
            </ul>
        </nav>
    </header>
    <h2>Staff signup</h2>
    <form action="" method="POST" onsubmit="return validateForm()">
        <input type="text" name="name" placeholder="Enter your name" required>
        <input type="email" name="email" id="email" placeholder="Enter your email" required>
        <input type="password" name="password" id="password" placeholder="Enter your password" required onkeyup="checkPasswordStrength()">
        <div id="password-strength">
            <div id="password-strength-bar"></div>
        </div><br>
        <button type="submit">Send OTP</button>
    </form>

    <script>
        function validateForm() {
            const email = document.getElementById('email').value;
            const emailPattern = /^[a-zA-Z0-9._%+-]+@mgmcen\.ac\.in$/;
            if (!email.match(emailPattern)) {
                alert("Please enter a valid email address ending with @mgmcen.ac.in.");
                return false;
            }

            const password = document.getElementById('password').value;
            const passwordPattern = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;"'<>,.?/~`|\\=-]).+$/;
            if (!password.match(passwordPattern)) {
                alert("Password must contain at least one capital letter and one symbol.");
                return false;
            }

            return true;
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('password-strength-bar');
            let strength = 0;

            // Check for different conditions for strength
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
            if (/\d/.test(password)) strength += 1;

            switch (strength) {
                case 0:
                    strengthBar.style.width = '0%';
                    break;
                case 1:
                    strengthBar.style.width = '25%';
                    strengthBar.style.backgroundColor = 'red';
                    break;
                case 2:
                    strengthBar.style.width = '50%';
                    strengthBar.style.backgroundColor = 'orange';
                    break;
                case 3:
                    strengthBar.style.width = '75%';
                    strengthBar.style.backgroundColor = 'yellow';
                    break;
                case 4:
                    strengthBar.style.width = '100%';
                    strengthBar.style.backgroundColor = 'green';
                    break;
            }
        }
    </script>
</body>
</html>
