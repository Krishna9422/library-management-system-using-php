<?php
include 'db.php';

$response = ['success' => false];

if (isset($_GET['isbn']) || isset($_GET['title'])) {
    $isbn = $_GET['isbn'] ?? '';
    $title = $_GET['title'] ?? '';

    $query = "
        SELECT DISTINCT title, isbn 
        FROM book_copies 
        WHERE isbn = ? OR title = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $isbn, $title);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = [
            'success' => true,
            'data' => $result->fetch_assoc()
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
