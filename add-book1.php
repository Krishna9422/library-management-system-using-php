<?php
// Include database connection
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $publishing_year = (int)$_POST['publishing_year'];
    $total_copies = (int)$_POST['total_copies'];
    $sid = (int)$_POST['sid'];

    // Check if the book already exists
    $check_sql = "SELECT * FROM books WHERE sid = '$sid'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Book with this SID already exists
        echo "<script>alert('Error: SID already exists. Please use a different SID.'); window.location.href='add-book.php';</script>";
    } else {
        // Insert a new record
        $insert_sql = "INSERT INTO books (title, author, publishing_year, total_copies, sid) VALUES ('$title', '$author', $publishing_year, $total_copies, $sid)";
        if ($conn->query($insert_sql)) {
            echo "<script>alert('Successfully added new book: \"$title\" by $author.'); window.location.href='add-book.php';</script>";
        } else {
            echo "<script>alert('Error adding the book: " . $conn->error . "'); window.location.href='add-book.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <link rel="stylesheet" href="./style/add_books.css">
</head>
<body>
<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul class="list">
                <li><a href="http://localhost/library_final/home2.php">Home</a></li>
                <li><a href="http://localhost/library_final/books1.php">Books</a></li>
             
                <li><a href="http://localhost/library_final/return-book1.php">Return  Books</a></li>
                <li><a href="http://localhost/library_final/loan_book1.php">Loan Book</a></li>
               
                <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                <li><a href="http://localhost/library_final/fine1.php">Fine</a></li>
                <li><a href="http://localhost/library_final/borrowers11.php">Borrower </a></li>
                <li><a href="http://localhost/library_final/loan1.php">Loaned Book</a></li>
                <li><a href="http://localhost/library_final/overduebook1.php">overdue books</a></li>   
                </ul>
            </li>
            </ul>
        </nav>
</header>

<h1>Add a New Book</h1>

<main>
    <form method="POST" action="add-book.php">
        <label for="title">Book Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="author">Author:</label>
        <input type="text" name="author" id="author" required>

        <label for="publishing_year">Publishing Year:</label>
        <input type="number" name="publishing_year" id="publishing_year" required>

        <label for="total_copies">Total Copies:</label>
        <input type="number" name="total_copies" id="total_copies" min="1" required>

        <label for="sid">SID:</label>
        <input type="number" name="sid" id="sid" required>

        <input type="submit" value="Add Book">
    </form>
</main>
</body>
</html>
