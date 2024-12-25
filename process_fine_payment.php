<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "librarydb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $borrower_id = intval($_POST["borrower_id"]);
    $total_fine = floatval($_POST["total_fine"]);
    
    // Insert into fine table
    $query = "INSERT INTO fine (borrower_id, total_fine, paid_date) VALUES (?, ?, CURDATE())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("id", $borrower_id, $total_fine);
    $stmt->execute();

    // Delete rows from loans table for the borrower
    $query = "DELETE FROM loans WHERE borrower_id = ? AND due_date < CURDATE()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $borrower_id);
    $stmt->execute();

    echo "<p>Fine of ₹{$total_fine} paid successfully for Borrower ID {$borrower_id}.</p>";
    echo "<a href='fine_payment_form.html'>Go Back</a>";

    $stmt->close();
}

$conn->close();
?>
