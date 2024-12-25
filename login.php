<?php
session_start();
include 'db.php';

// Initialize error message
$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email exists in the database
    $sql = "SELECT id, borrower_name, password FROM borrowers WHERE borrower_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $borrower = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $borrower['password'])) {
            // Password is correct, store session data
            $_SESSION['borrower_id'] = $borrower['id'];
            $_SESSION['borrower_name'] = $borrower['borrower_name'];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrower Login</title>
    <link rel="stylesheet" href="./style/return.css">
    <style> 
    h2 {
    text-align: center;
    color: #4A4A4A;
    font-size: 24px;
    margin-top: 20px;}      
header {
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
    text-decoration: underline;}
    </style>
</head>
<body>
<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        
        <nav>
            <ul class="list">
                <li><a href="index.html">Home</a></li>
                <li><a href="http://localhost/library_final/book.php">Books</a></li>
                <li><a href="http://localhost/library_final/manager-login.php">Staff Login</a></li>
                <li><a href="http://localhost/library_final/login.php">Student Login</a></li>
            </ul>
        </nav>
</header>
    <h2 >Student Login</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    
    <form action="login.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        
        <input type="submit" name="login" value="Login">
    </form>
    <p>Don't have an account? <a href="http://localhost/library_final/student_signup.html">Sign up here</a></p>
   <p><a href="http://localhost/library_final/forgotpass.php">Forgot Password</a></p>
</body>
</html>
