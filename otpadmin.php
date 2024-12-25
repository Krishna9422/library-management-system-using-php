<?php
include 'db.php';  // Include database connection details
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get OTP from user and email
    $otp = $_POST['otp'];
    $email = $_POST['email'];

    // Check if OTP is correct
    $stmt = $conn->prepare("SELECT * FROM verification_requests WHERE email = ? AND otp = ?");
    $stmt->bind_param("si", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // OTP is correct, update verification status
        $stmt = $conn->prepare("UPDATE verification_requests SET otp_verified = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Send email to admin that OTP was verified and user has been verified
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server (e.g., Gmail, SendGrid)
            $mail->SMTPAuth = true;
            $mail->Username = 'Your@mail.com'; // SMTP username
            $mail->Password = 'passsword'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('Your@mail.com', 'Your Website');
            $mail->addAddress('admin gmail', 'Admin'); // Admin email

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'OTP Verified - New User Sign-Up';
            $mail->Body    = "Hello Admin,<br><br>The following user has successfully verified their email and OTP:<br><strong>Email:</strong> $email<br><strong>Name:</strong> <i>From your database (or add as needed)</i><br><br>Thank you.";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        echo "<script>
        alert('Request end successfully, wait for admin permission.');
        window.location.href='index.html';
      </script>"; 
        // Redirect or show a success message
    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        background-color: violet;
        border-bottom: 1px solid #ddd;
        }
        .logo {
        width: 60px; /* Adjust logo size as necessary */
        /* height: auto; */
        display: block; /* Ensures it behaves like a block element */
        margin: 5px /* Centers the logo */
        
        }
        .logo img {
        height: 50px; /* Adjust logo size as needed */
        margin-right: 10px;
        }
        
        nav {
        flex: 1;
        display: flex;
        justify-content: right;
        }
        
        nav ul.list {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        }
        
        nav ul.list li {
        margin: 0 15px;
        }
        
        nav ul.list li a {
        text-decoration: none;
        color: #333;
        font-size: 18px;
        font-weight: 600;
        transition: color 0.3s;
        }
        
        nav ul.list li a:hover {
        color: #007bff; /* Change color on hover */
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
            box-sizing: border-box; /* Ensures padding does not affect the width */
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
        button{
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
        
        /* Sign-up link */
        p {
            text-align: center;
            margin-top: 15px;
        }
        
        p a {
            color: violet;
            font-weight: bold;
            text-decoration: none;
        }
        
        p a:hover {
            text-decoration: underline;
        }
        .list {
                list-style-type: none;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: space-around;
                align-items: center;
            }
        
            .list li {
                position: relative;
            }
        
            .list li a {
                text-decoration: none;
                color: white;
                font-size: 16px;
                font-weight: bold;
                padding: 8px 16px;
                display: block;
            }
        
            .list li a:hover {
                background-color: #fc25f8;
                border-radius: 5px;
            }
              .dropdown:hover.dropdown-menu {
                display: block;
            }
        
            .dropdown-menu {
        
                position: absolute;
                background-color: white;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                border-radius: 5px;
                list-style: none;
                padding: 10px 0;
                min-width: 150px;
                top: 100%; /* Positions dropdown below the parent menu */
                left: 0;
            }
        
            .dropdown-menu li a {
                color: #333;
                padding: 10px 16px;
            }
        
            .dropdown-menu li a:hover {
                background-color: #f0f0f0;
                color: #6a11cb;
                border-radius: 5px;
            }
            .dropdown {
            position: relative; /* Ensure dropdown is positioned relative to its parent */
        }
        
        .dropdown:hover .dropdown-menu {
            display: block; /* Show dropdown on hover */
        }
        
        .dropdown-menu {
            display: none; /* Hide dropdown by default */
            position: absolute;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            list-style: none;
            padding: 10px 0;
            min-width: 150px;
            top: 100%; /* Positions dropdown below the parent menu */
            left: 0;
        }
        
        .dropdown-menu li a {
            color: #333;
            padding: 10px 16px;
            text-decoration: none; /* Remove underline from links */
        }
        
        .dropdown-menu li a:hover {
            background-color: #f0f0f0;
            color: #6a11cb;
            border-radius: 5px;
        }
        h2 {
            text-align: center;
            color: #4A4A4A;
            font-size: 24px;
            margin-top: 20px;
        }
        button{  background-color: #7c4dff; /* Primary button color */
    color: #ffffff; /* White text for contrast */
    border: none; /* Remove default border */
    padding: 15px; /* Extra padding for a larger button */
    font-size: 18px; /* Larger text */
    border-radius: 4px; /* Rounded button */
    cursor: pointer; /* Show pointer on hover */
    transition: background-color 0.3s ease; }

    button:hover {
    background-color: #5e35b1; /* Darker button on hover */
}
        </style>
    <link rel="stylesheet" href="./style/return.css">
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
        <form action="" method="POST">
    <input type="email" name="email" placeholder="Enter your email" required>
    <input type="text" name="otp" placeholder="Enter OTP" required>
    <button type="submit">Verify OTP</button>
</form>

</body>
</html>
