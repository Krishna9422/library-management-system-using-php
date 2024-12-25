<?php
include 'db.php'; // Include the database connection

// Get ISBN and Book ID from the GET parameters
$isbn = $_GET['isbn'];
$book_id = $_GET['book_id'];

// SQL query to fetch borrower details along with borrow date
$sql = "
    SELECT 
        l.borrower_id,
        b.borrower_name,
        l.referral_code,
        l.borrow_date
    FROM loans l
    JOIN borrowers b ON l.borrower_id = b.id
    WHERE l.isbn = '$isbn' AND l.book_id = '$book_id'
";

$result = $conn->query($sql);

$borrowers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $borrowers[] = $row;
    }
}

// Return the borrower data as JSON
echo json_encode($borrowers);
?>
