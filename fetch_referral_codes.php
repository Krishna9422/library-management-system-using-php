<?php
include 'db.php';

if (isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    
    // Fetch referral codes for the selected book
    $referralQuery = "SELECT referral_code FROM book_copies WHERE book_id = ?";
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

