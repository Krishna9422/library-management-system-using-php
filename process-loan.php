<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $borrower_id = $_POST['borrower_id'];
    $title = $_POST['title'];
    $referral_code = $_POST['referral_code'];
    $borrow_date = $_POST['borrow_date'];
    $isbn = $_POST['isbn'];

    // Calculate due_date
    $borrowDateTime = new DateTime($borrow_date);
    $dueDateTime = $borrowDateTime->modify('+15 days');
    $due_date = $dueDateTime->format('Y-m-d');

    // Find the book_id using the selected ISBN from the books table
    $bookQuery = "SELECT book_id FROM books WHERE isbn = ?";
    $stmt = $conn->prepare($bookQuery);
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if ($book) {
        $book_id = $book['book_id'];

        // Insert the loan record into the loan table with book_id and due_date
        $loanQuery = "INSERT INTO loans (borrower_id, book_id, referral_code, borrow_date, due_date, isbn) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($loanQuery);
        $stmt->bind_param("iissss", $borrower_id, $book_id, $referral_code, $borrow_date, $due_date, $isbn);

        if ($stmt->execute()) {
            // Update the loaned_copies count in the books table
            $updateBookQuery = "UPDATE books SET loaned_copies = loaned_copies + 1 WHERE book_id = ?";
            $stmt = $conn->prepare($updateBookQuery);
            $stmt->bind_param("i", $book_id);
            $stmt->execute();

            // Redirect to loaned.php with success message
            header("Location: http://localhost/library_final/loan_book.php?message=Loan successfully processed!");
            exit();
        } else {
            echo "Error: Could not save the loan record. " . $conn->error;
        }
    } else {
        echo "Error: Could not find the book associated with the selected ISBN.";
    }
}
?>
