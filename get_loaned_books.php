<?php
include 'db.php';

if (isset($_GET['borrower_id'])) {
    $borrower_id = $_GET['borrower_id'];
    
    $sql = "
        SELECT 
            bc.title, 
            bc.isbn, 
            l.referral_code, 
            l.borrow_date 
        FROM 
            loans l
        JOIN 
            books bc ON l.book_id = bc.book_id
        WHERE 
            l.borrower_id = ?
        GROUP BY 
            l.book_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $borrower_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<p>{$row['title']} | ISBN: {$row['isbn']} | Code: {$row['referral_code']} | Date: {$row['borrow_date']}</p>";
        }
    } else {
        echo "<p>No books loaned.</p>";
    }

    $stmt->close();
}
?>
