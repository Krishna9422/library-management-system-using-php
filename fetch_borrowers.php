<?php
include 'db.php';

// Check if ISBN is provided in the GET request
if (isset($_GET['isbn'])) {
    $isbn = $conn->real_escape_string($_GET['isbn']);

    // Query to get the borrower details for the specific ISBN
    $sql = "
        SELECT 
            b.id AS borrower_id,  -- Use the correct column name
            b.borrower_name, 
            l.loan_date
        FROM loans l
        JOIN borrowers b ON l.borrower_id = b.id   -- Ensure this matches the actual foreign key relationship
        WHERE l.isbn = '$isbn'
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $borrowers = [];
        while ($row = $result->fetch_assoc()) {
            $borrowers[] = $row;
        }

        // Return borrowers in JSON format
        echo json_encode($borrowers);
    } else {
        // No borrowers found for this ISBN
        echo json_encode(['error' => 'No borrowers found for this ISBN']);
    }
} else {
    echo json_encode(['error' => 'Invalid ISBN']);
}
?>
