<?php
include 'db.php';

if (isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    
    // Fetch referral codes for the selected book that are not in the loan table
    $referralQuery = "
        SELECT bc.referral_code 
        FROM book_copies bc 
        LEFT JOIN loan l ON bc.referral_code = l.referral_code 
        WHERE bc.book_id = ? AND l.referral_code IS NULL
    ";
    
    $stmt = $conn->prepare($referralQuery);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Generate options for referral codes
    echo '<option value="">Select Referral Code</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['referral_code']) . '">' . htmlspecialchars($row['referral_code']) . '</option>';
    }
    
    $stmt->close();
}
?>