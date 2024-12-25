<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if the form is submitted
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email exists in the managers table
    $sql = "SELECT id, password FROM managers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a manager with the given email is found
    if ($result->num_rows > 0) {
        $manager = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $manager['password'])) {
            // Store manager ID in the session
            $_SESSION['manager_id'] = $manager['id'];

            // Show alert for successful login
            echo "<script>alert('Login successful!');</script>";

            // Redirect to manager dashboard
            header("Location: home2.php");
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "Email not found. Please check your email or sign up.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login</title>
    <link rel="stylesheet" href="./style/return.css">
    <style>
        h2 {
            text-align: center;
            color: #4A4A4A;
            font-size: 24px;
            margin-top: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: violet;
            border-bottom: 1px solid #ddd;
        }

        .logo {
            width: 60px;
            display: block;
            margin: 5px;
        }

        .logo img {
            height: 50px;
        }

        nav {
            flex: 1;
            display: flex;
            justify-content: flex-end;
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
            color: #007bff;
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
            border-radius: 5px;
        }

        .dropdown-menu {
            display: none;
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

        .dropdown:hover .dropdown-menu {
            display: block; /* Show dropdown on hover */
        }

    </style>
</head>
<body>
<header>  
    <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    <nav>
        <ul class="list">
            <li><a href="index.html">Home</a></li>
            <li><a href="http://localhost/library_final/book.php">Books</a></li>
            <li class="dropdown">
                <a href="#"><b>Login</b></a>
                <ul class="dropdown-menu">
                    <li><a href="http://localhost/library_final/admin-login.php">Admin Login</a></li>
                    <li><a href="http://localhost/library_final/manager-login.php">Staff Login</a></li>
                    <li><a href="http://localhost/library_final/login.php">Student Login</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<h2>Staff Login</h2>

<!-- Show error message if any -->
<?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

<form action="manager-login.php" method="POST">
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    
    <input type="submit" name="login" value="Login">
</form>

<p>Don't have an account? <a href="signup.php">Sign up here</a></p>
</body>
</html>
