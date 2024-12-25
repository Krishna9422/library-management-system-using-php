<?php
session_start();
include 'db.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Server-side validation for name
    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        $error = "Name should only contain alphabets.";
    } 
    // Server-side validation for email
    elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@mgmcen\.ac\.in$/", $email)) {
        $error = "Only '@mgmcen.ac.in' emails are allowed.";
    } 
    // Server-side validation for password
    elseif (strlen($password) < 8 || 
            !preg_match("/[A-Z]/", $password) || 
            !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $error = "Password must be at least 8 characters long, contain at least one uppercase letter, and one special character.";
    } 
    else {
        // Check if the email already exists
        $checkEmailQuery = "SELECT * FROM managers WHERE email = ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new manager into the database
            $insertQuery = "INSERT INTO managers (name, email, password) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($insertStmt->execute()) {
                $success = true; // Set success flag
            } else {
                $error = "There was an error creating your account. Please try again.";
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
    <title>Manager Signup</title>
    <link rel="stylesheet" href="./style/return.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        header {
            background-color:rgb(220, 8, 174);
            padding: 10px;
            color: white;
            text-align: center;
        }
        header img.logo {
            height: 50px;
            vertical-align: middle;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin-right: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        h2 {
            text-align: center;
            margin-top: 20px;
        }
        form {
            max-width: 500px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        form input[type="text"],
        form input[type="email"],
        form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color:rgb(128, 0, 113);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color:rgb(102, 0, 44);
        }
        .error {
            color: red;
            margin-bottom: 10px;
            text-align: center;
        }
        #strength-bar {
            height: 5px;
            background: lightgray;
            margin-bottom: 5px;
        }
        #strength-bar div {
            height: 100%;
            width: 0;
            background: red;
        }
        #strength-text {
            font-size: 14px;
            color: #333;
        }
        #password-strength {
            margin-top: 5px;
            font-size: 14px;
            font-weight: bold;
            color: red;
        }
        .validation-message { color: red; font-size: 12px; margin-bottom: 10px; display: none; }
    </style>
</head>
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

    <input type="submit" value="Sign Up">
</form>

<script>
<?php if ($success): ?>
    // Show success popup if signup is successful
    window.onload = function() {
        alert("Signup successful! You can now log in.");
        window.location.href = 'manager-login.php'; // Redirect to login page
    };
<?php endif; ?>

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
