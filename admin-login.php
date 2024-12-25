<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="./style/return.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: violet;
            border-bottom: 1px solid #ddd;
        }
        .logo img {
            height: 50px;
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
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        nav ul li {
            margin: 0 15px;
        }
        nav ul li a {
            text-decoration: none;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        nav ul li a:hover {
            color: #007bff;
        }
        h2 {
            text-align: center;
            color: #4A4A4A;
            margin-top: 20px;
        }
        form {
            width: 100%;
            max-width: 400px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
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
        }
        form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: violet;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color: #5f3db5;
        }
        .error {
            color: red;
            font-size: 14px;
            text-align: center;
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
                    <li><a href="http://localhost/library_final/admin-login.php">admin login</a></li>
                    <li><a href="http://localhost/library_final/manager-login.php">Staff Login</a></li>
                    <li><a href="http://localhost/library_final/login.php">Student Login</a></li>
                    
                </ul>
            </li>
        </ul>
    </nav>
</header>
<h2>Admin Login</h2>
<form id="loginForm">
    <div id="error" class="error"></div>
    <label for="email">Email:</label>
    <input type="email" id="email" required>

    <label for="password">Password:</label>
    <input type="password" id="password" required>

    <input type="submit" value="Login">
</form>
<script>
    document.getElementById('loginForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from submitting
        
        // Clear previous error messages
        const errorDiv = document.getElementById('error');
        errorDiv.textContent = '';

        // Get form values
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorDiv.textContent = 'Please enter a valid email address.';
            return;
        }

        // Validate password length
        if (password.length < 6) {
            errorDiv.textContent = 'Password must be at least 6 characters long.';
            return;
        }

        // Dummy check for email and password (replace with server-side check)
        if (email === "mail" && password === "password") {
            alert("Login successful!");
            // Redirect to another page
            window.location.href = "http://localhost/library_final/home.php";
        } else {
            errorDiv.textContent = 'Invalid email or password.';
        }
    });
</script>
</body>
</html>
