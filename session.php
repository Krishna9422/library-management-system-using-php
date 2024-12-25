<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "librarydb"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form inputs
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $publishing_year = (int)$_POST['publishing_year'];
    $total_copies = (int)$_POST['total_copies'];
    $isbn = $conn->real_escape_string($_POST['isbn']);

    // Insert the book record into the books table
    $sql_insert_book = "INSERT INTO books (title, author, publishing_year, total_copies, isbn) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert_book);
    $stmt->bind_param("ssiis", $title, $author, $publishing_year, $total_copies, $isbn);

    if ($stmt->execute()) {
        echo "<script>alert('New book added successfully.');</script>";

        // Call the stored procedure to generate book copies without any arguments
        $sql_generate_copies = "CALL GenerateBookCopies11()";
        $stmt_generate = $conn->prepare($sql_generate_copies);

        if ($stmt_generate->execute()) {
            echo "<script>alert('Book copies generated successfully.');</script>";
        } else {
            $error_message = "Error generating book copies: " . $conn->error;
        }
        $stmt_generate->close();
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <link rel="stylesheet" href="./style/add_books.css">
    <style>
        /* Add your existing CSS styles here */
    </style>
    <script>
        // Detect back button navigation
        window.addEventListener("popstate", function (event) {
            // Redirect to home.php if the back button is pressed
            window.location.href = "home.php";
        });

        // Push an empty state to the history stack to handle back navigation
        history.pushState(null, "", window.location.href);
    </script>
</head>
<body>
<header>  
    <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    <nav>
        <ul class="list">
            <li><a href="http://localhost/library_final/home.php">Home</a></li>
            <li><a href="http://localhost/library_final/books.php">Books</a></li>
            <li><a href="http://localhost/library_final/add-book.php">Add New Book</a></li>
            <li><a href="http://localhost/library_final/return-book.php">Return  Books</a></li>
            <li><a href="http://localhost/library_final/loan_book.php">Loan Book</a></li>
            <li><a href="http://localhost/library_final/borrowers.php">Student Id</a></li>
            <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                    <li><a href="http://localhost/library_final/fine.php">Fine</a></li>
                    <li><a href="http://localhost/library_final/borrowers1.php">Borrower</a></li>
                    <li><a href="http://localhost/library_final/loan.php">Loaned Book</a></li>
                    <li><a href="http://localhost/library_final/overduebook.php">Overdue Books</a></li>   
                </ul>
            </li>
        </ul>
    </nav>
</header>

<h1>Add a New Book</h1>

<main>
    <form method="POST" action="http://localhost/library_final/add-book.php">
        <label for="title">Book Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="author">Author:</label>
        <input type="text" name="author" id="author" required>

        <label for="publishing_year">Publishing Year:</label>
        <input type="number" name="publishing_year" id="publishing_year" required min="1000" max="9999">

        <label for="total_copies">Total Copies:</label>
        <input type="number" name="total_copies" id="total_copies" min="1" required>

        <label for="isbn">ISBN:</label>
        <input type="text" name="isbn" id="isbn" required>

        <input type="submit" value="Add Book">
    </form>
</main>

<?php
// Display the error in a popup if it exists
if (!empty($error_message)) {
    echo "<script>alert('$error_message');</script>";
}
?>
</body>
</html>
