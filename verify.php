<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        // Save user to database
        $name = $_SESSION['name'];
        $email = $_SESSION['email'];
        $password = $_SESSION['password'];

        $sql = "INSERT INTO managers (name, email, password) VALUES ('$name', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
            alert('Registration successful!');
            window.location.href = 'login.php';  // Redirect to login page
          </script>";
            session_destroy(); // Clear session
            // Redirect to login page
            
            header("Location: login.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        // Invalid OTP - Show alert and redirect to registration page
        echo "<script>
                alert('Invalid OTP. Please try again.');
                window.location.href = 'register.php';  // Redirect to registration page
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="./style/loan.css">
    <style>
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
        h2 {
            text-align: center;
            color: #4A4A4A;
            font-size: 24px;
            margin-top: 20px;
        }
      nav {
        background-color: violet;
        padding: 10px;
    }
  h2{}
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
</head>
<body>
<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul class="list">
               
        </nav>
</header>
    <h2>Verify OTP</h2>
    <form method="POST" action="">
        <label for="otp">Enter OTP:</label>
        <input type="number" id="otp" name="otp" required><br>
        <button type="submit" name="verify">Verify</button>
    </form>
</body>
</html>
