<?php
session_start();
include 'db.php'; // Connect to the database

if (isset($_POST['borrower_id'], $_POST['book_id'], $_POST['borrow_date'])) {
    $borrower_id = $_POST['borrower_id'];
    $book_id = $_POST['book_id'];
    $borrow_date = $_POST['borrow_date'];

    // Check if the borrower exists, and the book exists and is available
    $sql = "
        SELECT b.id AS borrower_id, bk.book_id, bk.total_copies - IFNULL(SUM(l.borrower_id IS NOT NULL), 0) AS available_copies
        FROM borrowers b
        JOIN books bk ON bk.book_id = ?
        LEFT JOIN loans l ON l.book_id = bk.book_id AND l.return_date IS NULL
        WHERE b.id = ?
        GROUP BY bk.book_id, b.id
        HAVING available_copies > 0";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $book_id, $borrower_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // If the book is available and borrower exists, insert into loans
        $insert_sql = "INSERT INTO loans (borrower_id, book_id, borrow_date) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iis", $borrower_id, $book_id, $borrow_date);

        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "Book loaned successfully!";
            header("Location: borrowers.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to loan the book. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Either the borrower doesn't exist or no available copies of this book.";
    }
} else {
    $_SESSION['error'] = "All fields are required.";
}

// Redirect back to the loan form if there's an error
header("Location: loan-book-form.php");
exit();
?>
