<?php
// Include the PHPMailer library (adjust the path as necessary)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';

// Database connection
include 'db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['return_book'])) {
    $book_id = $_POST['book_id'];
    $borrower_id = $_POST['borrower_id'];
    $return_date = $_POST['return_date'];

    // 1. Return book and insert data into the 'return' table
    $sql_return = "INSERT INTO return (borrower_id, isbn, referral_code, return_date)
                   SELECT borrower_id, isbn, referral_code, ? FROM loans WHERE book_id = ?";
    $stmt = $conn->prepare($sql_return);
    $stmt->bind_param("si", $return_date, $book_id);
    $stmt->execute();

    // 2. Update the 'loans' table to mark the book as returned
    $sql_update_loan = "DELETE FROM loans WHERE book_id = ?";
    $stmt_update = $conn->prepare($sql_update_loan);
    $stmt_update->bind_param("i", $book_id);
    $stmt_update->execute();

    // 3. Send email notification to borrower
    $borrower_email_sql = "SELECT borrower_email FROM borrowers WHERE borrower_id = ?";
    $email_stmt = $conn->prepare($borrower_email_sql);
    $email_stmt->bind_param("i", $borrower_id);
    $email_stmt->execute();
    $email_result = $email_stmt->get_result();
    $borrower_email = $email_result->fetch_assoc()['borrower_email'];

    if ($borrower_email) {
        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'Your@mail.com'; // Your Gmail address
            $mail->Password = 'password'; // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('Your@mail.com', 'Library Management');
            $mail->addAddress($borrower_email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Book Return Confirmation';
            $mail->Body    = 'Your book has been successfully returned. Thank you!';

            $mail->send();
            echo 'Email has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email address not found for borrower.";
    }

    // 4. Show popup and hide the row (JavaScript)
    echo "<script>
        alert('Book returned successfully.');
        document.getElementById('loan_row_" . $book_id . "').style.display = 'none';
    </script>";
}

// Fetch borrower_id and display books loaned by the selected borrower
if (isset($_POST['borrower_id'])) {
    $borrower_id = $_POST['borrower_id'];
    $sql_books = "SELECT loans.book_id, books.title 
                  FROM loans 
                  JOIN books ON loans.book_id = books.book_id
                  WHERE loans.borrower_id = ?";
    $stmt_books = $conn->prepare($sql_books);
    $stmt_books->bind_param("i", $borrower_id);
    $stmt_books->execute();
    $books_result = $stmt_books->get_result();
} else {
    $books_result = null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book</title>
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
            display: block;
            margin: 5px;
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

        p.error {
            color: red;
            font-size: 14px;
            text-align: center;
        }

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

        .dropdown:hover .dropdown-menu {
            display: block;
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
            top: 100%;
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
    </style>
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

<!-- Form to input borrower_id, and then show books loaned by the borrower -->
<form method="POST">
    <label for="borrower_id">Select Borrower ID:</label>
    <select id="borrower_id" name="borrower_id" required>
        <option value="">Select Borrower ID</option>
        <?php
        $sql = "SELECT DISTINCT borrower_id FROM loans";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['borrower_id'] . "'>" . $row['borrower_id'] . "</option>";
        }
        ?>
    </select>
    <br><br>

    <?php if (isset($books_result)): ?>
        <label for="book_id">Select Book ID:</label>
        <select id="book_id" name="book_id" required>
            <option value="">Select Book ID</option>
            <?php
            while ($row = $books_result->fetch_assoc()) {
                echo "<option value='" . $row['book_id'] . "'>" . $row['book_id'] . " - " . $row['title'] . "</option>";
            }
            ?>
        </select>
        <br><br>
    <?php endif; ?>

    <label for="return_date">Return Date:</label>
    <input type="date" id="return_date" name="return_date" required>
    <br><br>

    <input type="submit" name="return_book" value="Return Book">
</form>
</body>
</html>
