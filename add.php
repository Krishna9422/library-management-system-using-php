<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library"; // Replace with your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$title = $_POST['title'];
$isbn = $_POST['isbn'];
$available_copies = $_POST['available_copies'];

// Insert the book record into the books table
$sql_insert_book = "INSERT INTO books (title, isbn, available_copies) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql_insert_book);
$stmt->bind_param("ssi", $title, $isbn, $available_copies);

if ($stmt->execute()) {
    echo "New book added successfully.<br>";

    // Get the last inserted book ID
    $new_book_id = $conn->insert_id;

    // Call the stored procedure to generate book copies only for the new book
    $sql_generate_copies = "CALL GenerateBookCopies7($new_book_id)";
    if ($conn->query($sql_generate_copies) === TRUE) {
        echo "Book copies generated successfully.";
    } else {
        echo "Error generating book copies: " . $conn->error;
    }
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
