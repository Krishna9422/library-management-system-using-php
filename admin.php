<?php
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'add') {
        // Fetch user details from the verification_requests table
        $stmt = $conn->prepare("SELECT name, email, password FROM verification_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Add user to managers table
        $stmt = $conn->prepare("INSERT INTO managers (name, email, password) VALUES (?, ?, ?)");
  // Default password
        $stmt->bind_param("sss", $user['name'], $user['email'], $user['password']);
        $stmt->execute();

        // Send confirmation email to the user using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server (e.g., Gmail, SendGrid)
            $mail->SMTPAuth = true;
            $mail->Username = 'Your@mail.com'; // SMTP username
            $mail->Password = 'app paassword'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('Your@mail.com', 'Your Website');
            $mail->addAddress($user['email'], $user['name']); // Add recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'You have been added as a staff member';
            $mail->Body    = "Hello " . $user['name'] . ",<br><br>You have been added as a staff member.<br><br>Please change it after logging in.";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Delete the request from verification_requests table
        $stmt = $conn->prepare("DELETE FROM verification_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo 'User added to staff and confirmation email sent.';
    } elseif ($action == 'deny') {
        // Fetch user details from the verification_requests table
        $stmt = $conn->prepare("SELECT name, email FROM verification_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Send denial email to the user using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server (e.g., Gmail, SendGrid)
            $mail->SMTPAuth = true;
            $mail->Username = 'Your@mail.com'; // SMTP username
            $mail->Password = 'app password'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('Your@mail.com', 'Your Website');
            $mail->addAddress($user['email'], $user['name']); // Add recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your request has been denied';
            $mail->Body    = "Hello " . $user['name'] . ",<br><br>We regret to inform you that your request has been denied.<br><br>Thank you.";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Delete the request from verification_requests table
        $stmt = $conn->prepare("DELETE FROM verification_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo 'User request denied and denial email sent.';
    }
}

// Fetching all OTP verified requests from the database
$sql = "SELECT * FROM verification_requests WHERE otp_verified = TRUE ORDER BY request_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - OTP Verification Requests</title>
    <link rel="stylesheet" href="./style/bor.css">
    <style>
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
    
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
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
                <li><a href="http://localhost/library_final/fine.php">Fine</a></li>
                <li><a href="http://localhost/library_final/borrowers1.php">Borrower </a></li>
                <li><a href="http://localhost/library_final/loan.php">Loaned Book</a></li>
                <li><a href="http://localhost/library_final/overduebook.php">overdue books</a></li>   
                </ul>
            </li>
            </ul>
        </nav>
</header>

<h2>OTP Verification Requests</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Action</th>
    </tr>

    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["id"]. "</td>
                    <td>" . $row["name"]. "</td>
                    <td>" . $row["email"]. "</td>
                    <td>
                        <a href='admin.php?action=add&id=" . $row["id"]. "'>Add to Staff</a> |
                        <a href='admin.php?action=deny&id=" . $row["id"]. "'>Deny</a>
                    </td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No pending requests</td></tr>";
    }
    ?>
</table>

</body>
</html>
