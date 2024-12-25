<?php
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $borrower_name = mysqli_real_escape_string($conn, $_POST['borrower_name']);
    $borrower_email = mysqli_real_escape_string($conn, $_POST['borrower_email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password securely

    // Prepare SQL query to insert the data into the database
    $query = "INSERT INTO borrowers (borrower_name, borrower_email, password) VALUES (?, ?, ?)";

    try {
        if ($stmt = $conn->prepare($query)) {
            // Bind parameters and execute
            $stmt->bind_param("sss", $borrower_name, $borrower_email, $password);
            $stmt->execute();
            $message = "Sign-up successful!";
            echo "<script>showMessage('Sign-up successful!', true);</script>";
            header("Location: http://localhost/library_final/login.php");
            exit; // Ensure no further code is executed after redirect
        }
    } catch (mysqli_sql_exception $e) {
        if ($conn->errno == 1062) { // Duplicate entry error
            $message = "User already exists with this email.";
            echo "<script>showMessage('User already exists with this email.', false);</script>";
        } else {
            $message = "An error occurred: " . $e->getMessage();
            echo "<script>showMessage('An error occurred: " . $e->getMessage() . "', false);</script>";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrower Sign Up</title>
    <link rel="stylesheet" href="./style/return.css">
    <style>
        h2 { text-align: center; color: #4A4A4A; font-size: 24px; margin-top: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: violet; border-bottom: 1px solid #ddd; }
        .logo { width: 60px; display: block; margin: 5px; }
        nav { flex: 1; display: flex; justify-content: right; }
        nav ul.list { display: flex; list-style: none; margin: 0; padding: 0; }
        nav ul.list li { margin: 0 15px; }
        nav ul.list li a { text-decoration: none; color: #333; font-size: 18px; font-weight: 600; transition: color 0.3s; }
        nav ul.list li a:hover { color: #007bff; }
        form { width: 100%; max-width: 400px; margin: 30px auto; padding: 20px; background-color: #f9f9f9; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        form label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        form input[type="email"], form input[type="text"], form input[type="password"] { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        form input[type="submit"] { width: 100%; padding: 10px; background-color: violet; border: none; border-radius: 4px; font-size: 16px; color: white; cursor: pointer; transition: background-color 0.3s ease; }
        form input[type="submit"]:hover { background-color: #5f3db5; }
        p.error { color: red; font-size: 14px; text-align: center; }
        p { text-align: center; margin-top: 15px; }
        p a { color: violet; font-weight: bold; text-decoration: none; }
        p a:hover { text-decoration: underline; }
        #password-strength { margin-top: 5px; font-size: 14px; font-weight: bold; color: red; }
        .validation-message { color: red; font-size: 12px; margin-bottom: 10px; display: none; }
    </style>
    <script>
        // Display popup message
        function showMessage(message, isSuccess) {
            alert(message); // Shows success or error message
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strength = document.getElementById("password-strength");
            const regexWeak = /^(?=.*[a-z]).{6,}$/;
            const regexMedium = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            const regexStrong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{10,}$/;

            if (regexStrong.test(password)) {
                strength.textContent = "Strong password";
                strength.style.color = "green";
            } else if (regexMedium.test(password)) {
                strength.textContent = "Medium password";
                strength.style.color = "orange";
            } else if (regexWeak.test(password)) {
                strength.textContent = "Weak password";
                strength.style.color = "red";
            } else {
                strength.textContent = "Very weak password";
                strength.style.color = "darkred";
            }
        }

        // Email domain validation
        function validateEmailDomain(email) {
            const emailField = document.getElementById("borrower_email");
            const errorMessage = document.getElementById("email-error");
            const validDomain = /@mgmcen\.ac\.in$/;

            if (!validDomain.test(email)) {
                errorMessage.style.display = "block";
                emailField.setCustomValidity("Only emails ending with @mgmcen.ac.in are allowed.");
            } else {
                errorMessage.style.display = "none";
                emailField.setCustomValidity("");
            }
        }
    </script>
</head>
<body>
    <header>
        <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul class="list">
                <li><a href="./index.html">Home</a></li>
                <li><a href="http://localhost/library_final/books.php">Books</a></li>
            </ul>
        </nav>
    </header>

    <h2>Student Sign Up</h2>
    <form action="http://localhost/library_final/student_signup.php" method="POST" onsubmit="validateEmailDomain(borrower_email.value)">
        <label for="borrower_name">Name:</label>
        <input type="text" id="borrower_name" name="borrower_name" required>

        <label for="borrower_email">Email:</label>
        <input type="email" id="borrower_email" name="borrower_email" required oninput="validateEmailDomain(this.value)">
        <p id="email-error" class="validation-message">Only emails ending with @mgmcen.ac.in are allowed.</p>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required oninput="checkPasswordStrength(this.value)">
        <div id="password-strength"></div>

        <input type="submit" name="signup" value="Sign Up">
    </form>

    <p>Already have an account? <a href="http://localhost/library_final/login.php">Login here</a></p>
</body>
</html>
