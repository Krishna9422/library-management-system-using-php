<?php
// Database connection
include 'db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch form data
$borrower_id = $_POST['borrower_id'];
$referral_code = $_POST['referral_code'];
$return_date = $_POST['return_date'];

// Find the loan entry based on borrower_id and referral_code
$sql = "SELECT l.*, b.isbn, b.book_id, l.borrow_date 
        FROM loans l 
        JOIN books b ON l.book_id = b.book_id 
        WHERE l.borrower_id = ? 
        AND l.referral_code = ? 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $borrower_id, $referral_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $loan = $result->fetch_assoc();
    $isbn = $loan['isbn'];
    $book_id = $loan['book_id'];
    $borrow_date = $loan['borrow_date'];

    // Validate book_id exists in books table
    $check_sql = "SELECT book_id FROM books WHERE book_id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("i", $book_id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();

    if ($check_result->num_rows > 0) {
        // Begin transaction
        $conn->begin_transaction();
        try {
            // Insert into returns table (with borrow_date)
            $sql_insert = "INSERT INTO returns (borrower_id, isbn, referral_code, return_date, borrow_date, book_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("issssi", $borrower_id, $isbn, $referral_code, $return_date, $borrow_date, $book_id);
            $stmt_insert->execute();

            // Delete from loans table
            $sql_delete = "DELETE FROM loans WHERE id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("i", $loan['id']);
            $stmt_delete->execute();

            // Update books table
            $sql_update = "UPDATE books 
                           SET loaned_copies = loaned_copies - 1, available_copies = available_copies + 1 
                           WHERE book_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $book_id);
            $stmt_update->execute();

            // Commit transaction
            $conn->commit();
            echo "<script>
                alert('Book returned successfully!');
                window.location.href = 'return-book.php';
                </script>";
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $conn->rollback();
            echo "<script>
                alert('Error: " . addslashes($e->getMessage()) . "');
                window.location.href = 'return-book.php';
                </script>";
        }
    } else {
        echo "<script>
            alert('Error: The book_id does not exist in the books table.');
            window.location.href = 'return-book.php';
            </script>";
    }
} else {
    echo "<script>
        alert('No matching loan found for the provided details.');
        window.location.href = 'return-book.php';
        </script>";
}

$conn->close();
?>
