<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Loans and Fine Payment</title>
    <link rel="stylesheet" href="./style/return.css">
    <link rel="stylesheet" href="./style/return.css">
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
}</style>
    <script>
        // Detect navigation type and redirect for back button
        window.addEventListener("pageshow", function (event) {
            if (event.persisted || performance.navigation.type === 2) {
                window.location.href = "http://localhost/library_final/home2.php";
            }
        });

        // Show the popup when the book is returned successfully
        function showPopup() {
            alert("The book has been successfully returned and the email has been sent.");
        }
    </script>
</head>
<body>
<header>  
    <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    <nav>
        <ul class="list">
            <li><a href="http://localhost/library_final/home2.php">Home</a></li>
            <li><a href="http://localhost/library_final/books1.php">Books</a></li>
            <li><a href="http://localhost/library_final/return-book1.php">Return Books</a></li>
            <li><a href="http://localhost/library_final/loan_book1.php">Loan Book</a></li>
            <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                    <li><a href="http://localhost/library_final/fine1.php">Fine</a></li>
                    <li><a href="http://localhost/library_final/borrowers11.php">Borrower</a></li>
                    <li><a href="http://localhost/library_final/loan1.php">Loaned Book</a></li>
                    <li><a href="http://localhost/library_final/overduebook1.php">Overdue Books</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
<h1>Return Book</h1>
<form method="POST">
    <label for="borrower_id">Select Borrower ID:</label>
    <select id="borrower_id" name="borrower_id" required>
        <option value="">Select Borrower ID</option>
        <?php
        include 'db.php';
        $sql = "SELECT DISTINCT borrower_id FROM loans";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['borrower_id'] . '">' . $row['borrower_id'] . '</option>';
        }
        ?>
    </select>
    <button type="submit">Show Loans</button>
</form>
<br><br>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrower_id'])) {
    $borrower_id = intval($_POST['borrower_id']);
    $current_date = date('Y-m-d');
    $sql = "SELECT loans.id, loans.borrower_id, loans.book_id, loans.due_date, books.title, loans.referral_code, loans.isbn, borrowers.borrower_email
            FROM loans
            JOIN books ON loans.book_id = books.book_id
            JOIN borrowers ON loans.borrower_id = borrowers.id
            WHERE loans.borrower_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $borrower_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h3>Loan Details for Borrower ID: $borrower_id</h3>";
        echo "<table border='1'>
                <tr>
                    <th>Borrower ID</th>
                    <th>Book Title</th>
                    <th>Due Date</th>
                    <th>Referral Code</th>
                    <th>ISBN</th>
                    <th>Fine</th>
                    <th>Return</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $book_id = $row['book_id'];
            $due_date = $row['due_date'];
            $days_overdue = ceil((strtotime($current_date) - strtotime($due_date)) / (60 * 60 * 24));
            $fine_amount = $days_overdue > 0 ? $days_overdue * 2 : 0;
            $borrower_email = $row['borrower_email']; // Fetch the borrower's email
            echo "<tr>
                    <td>{$row['borrower_id']}</td>
                    <td>{$row['title']}</td>
                    <td>{$due_date}</td>
                    <td>{$row['referral_code']}</td>
                    <td>{$row['isbn']}</td>
                    <td>{$fine_amount} INR</td>
                    <td>
                        <form method='POST'>
                            <input type='hidden' name='loan_id' value='{$row['id']}'>
                            <input type='hidden' name='borrower_id' value='{$borrower_id}'>
                            <input type='hidden' name='email' value='{$borrower_email}'> <!-- Pass the email here -->
                            <input type='hidden' name='book_title' value='{$row['title']}'> <!-- Pass the book title here -->
                            <button type='submit' name='return_book'>Return Book</button>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No loans found for this borrower.</p>";
    }
}

if (isset($_POST['return_book'])) {
    $loan_id = intval($_POST['loan_id']);
    $borrower_id = intval($_POST['borrower_id']);
    $return_date = date('Y-m-d');
    $email = $_POST['email']; // Fetch the email from the form data
    $book_title = $_POST['book_title']; // Fetch the book title from the form data

    $return_sql = "INSERT INTO `returns` (borrower_id, book_id, referral_code, isbn, return_date, borrow_date) 
                   SELECT borrower_id, book_id, referral_code, isbn, ?, borrow_date 
                   FROM loans WHERE id = ?";
    $return_stmt = $conn->prepare($return_sql);
    $return_stmt->bind_param("si", $return_date, $loan_id);
    $return_stmt->execute();

    $delete_sql = "DELETE FROM loans WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $loan_id);
    $delete_stmt->execute();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'Your@mail.com';
        $mail->Password = 'app passsword';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('Your@mail.com', 'Library Management System');
        $mail->addAddress($email); // Use the email fetched from the database

        $mail->isHTML(true);
        $mail->Subject = 'Book Return Confirmation';
        $mail->Body = "Dear Borrower,<br><br>The book with title <strong>{$book_title}</strong> has been successfully returned.<br><br>Thank you,<br>Library Management System.";

        $mail->send();
        echo "<script>showPopup();</script>"; // Show the popup
    } catch (Exception $e) {
        echo "<p>Book returned successfully, but email could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
    }
}

$conn->close();
?>

</body>
</html>
