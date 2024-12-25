<?php
session_start();
include 'db.php';

if (isset($_POST['signup'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

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
        $insertQuery = "INSERT INTO managers (email, password) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("ss", $email, $hashedPassword);
        
        if ($insertStmt->execute()) {
            $_SESSION['success'] = "Account created successfully! You can now log in.";
            header("Location: http://localhost/library_final/manager-login.php");
            exit();
        } else {
            $error = "There was an error creating your account. Please try again.";
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
</head>
<body>
<header>  
    <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    <nav>
        <ul class="list">
            <li><a href="./index.html">Home</a></li>
            <li><a href="http://localhost/library_final/book.php">Books</a></li>
        </ul>
    </nav>
</header>
<h2>Staff Signup</h2>
<?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<form action="signup.php" method="POST" id="signup-form">
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required onkeyup="checkPasswordStrength()">
    
    <div class="strength-bar" id="strength-bar">
        <div></div>
    </div>
    <div id="strength-text" class="strength-text">Password strength: Weak</div>
    
    <input type="submit" name="signup" value="Sign Up">
</form>

<p>Already have an account? <a href="http://localhost/library_final/manager-login.php">Login here</a></p>

<script><?php
session_start();
include 'db.php';

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
                $_SESSION['success'] = "Account created successfully! You can now log in.";
                header("Location: http://localhost/library_final/manager-login.php");
                exit();
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
        h2 { text-align: center; color: #4A4A4A; font-size: 24px; margin-top: 20px; }
        form { width: 100%; max-width: 400px; margin: 30px auto; padding: 20px; background-color: #f9f9f9; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        form label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        form input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        form input[type="submit"] { background-color: violet; border: none; color: white; cursor: pointer; transition: background-color 0.3s ease; }
        form input[type="submit"]:hover { background-color: #5f3db5; }
        p.error { color: red; font-size: 14px; text-align: center; }
        .strength-bar { height: 5px; width: 100%; margin-top: 5px; background-color: #e0e0e0; }
        .strength-bar div { height: 100%; width: 0%; background-color: red; }
        .strength-text { font-size: 14px; text-align: center; }
        .validation-message { color: red; font-size: 12px; margin-top: -10px; margin-bottom: 10px; display: none; }
    </style>
    <script>
        // Detect navigation type and redirect for back button
        window.addEventListener("pageshow", function (event) {
            if (event.persisted || performance.navigation.type === 2) {
                // Redirect to home.php if back button is pressed
                window.location.href = "http://localhost/library_final/home.php";
            }
        });
    </script>
</head>
<body>
<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul class="list">
                <li><a href="http://localhost/library_final/home.php">Home</a></li>
                <li><a href="http://localhost/library_final/books.php">Books</a></li>
               
                <li class="dropdown">
                <a href="#"><b>Add staff </b></a>
                <ul class="dropdown-menu">
                <li><a href="http://localhost/library_final/admin.php">Request</a></li>
                <li><a href="http://localhost/library_final/signup.php">Manually </a></li>
                </ul>
            </li>
                <li><a href="http://localhost/library_final/add-book.php">Add New Book</a></li>
                <li><a href="http://localhost/library_final/return-book.php">Return  Books</a></li>
                <li><a href="http://localhost/library_final/loan_book.php">Loan Book</a></li>
               
                <li><a href="http://localhost/library_final/borrowers.php">Student Id</a></li>
              
               
               
                <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                <li><a href="http://localhost/library_final/fine.php">fine</a></li>
                <li><a href="http://localhost/library_final/borrowers1.php">Borrower </a></li>
                <li><a href="http://localhost/library_final/loan.php">Loaned Book</a></li>
                <li><a href="http://localhost/library_final/overduebook.php">overdue books</a></li>   
                </ul>
            </li>
            </ul>
        </nav>
</header>s
<h2>Staff Signup</h2>
<?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

<form action="signup.php" method="POST" id="signup-form">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" required pattern="^[A-Za-z\s]+$" title="Name should only contain alphabets.">
    <p id="name-error" class="validation-message">Name should only contain alphabets.</p>
    
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required pattern="[a-zA-Z0-9._%+-]+@mgmcen\.ac\.in$" title="Only '@mgmcen.ac.in' emails are allowed.">
    <p id="email-error" class="validation-message">Only emails ending with @mgmcen.ac.in are allowed.</p>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required minlength="8" onkeyup="checkPassword()">
    <p id="password-error" class="validation-message"></p>
    
    <div class="strength-bar" id="strength-bar">
        <div></div>
    </div>
    <div id="strength-text" class="strength-text">Password strength: Weak</div>
    
    <input type="submit" name="signup" value="Sign Up">
</form>

<script>
    document.getElementById('signup-form').addEventListener('submit', function (event) {
        const emailField = document.getElementById('email');
        const emailError = document.getElementById('email-error');
        const email = emailField.value.trim();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@mgmcen\.ac\.in$/;

        if (!emailRegex.test(email)) {
            emailError.style.display = 'block';
            emailError.textContent = 'Only emails ending with @mgmcen.ac.in are allowed.';
            event.preventDefault();
        } else {
            emailError.style.display = 'none';
        }
    });

    function checkPassword() {
        const password = document.getElementById('password').value;
        const passwordError = document.getElementById('password-error');
        const strengthBar = document.getElementById('strength-bar').firstElementChild;
        const strengthText = document.getElementById('strength-text');

        let strength = 0;

        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[!@#$%^&*(),.?\":{}|<>]/.test(password)) strength++;

        const strengths = ['Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['red', 'orange', 'yellow', 'green'];

        strengthBar.style.width = (strength * 33) + '%';
        strengthBar.style.backgroundColor = colors[strength - 1] || 'red';
        strengthText.textContent = 'Password strength: ' + (strengths[strength - 1] || 'Weak');
    }
</script>
</body>
</html>

   


    <?php if (isset($_SESSION['success'])) { ?>
        alert("<?php echo $_SESSION['success']; ?>");
        <?php unset($_SESSION['success']); ?>
    <?php } ?>
</script>
</body>
</html>
