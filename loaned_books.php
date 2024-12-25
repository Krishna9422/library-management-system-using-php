<?php
// Include database connection
include 'db.php';

// Check if borrower_id is provided
if (isset($_GET['borrower_id'])) {
    $borrower_id = (int)$_GET['borrower_id'];

    // SQL query to fetch loaned books for the specific borrower
    $sql = "
        SELECT 
            l.id AS loan_id,
            b.title,
            l.borrow_date,
            l.return_date
        FROM 
            loans l
        JOIN 
            books b ON l.book_id = b.book_id
        WHERE 
            l.borrower_id = $borrower_id
    ";

    $result = $conn->query($sql);
} else {
    echo "No borrower specified.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loaned Books</title>
    <link rel="stylesheet" href="./style/bor.css">
    
</head>
<body>
<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        
        <nav>
            <ul class="list">
                <li><a href="http://localhost/library_final/home.php">Home</a></li>
                <li><a href="http://localhost/library_final/book.php">Books</a></li>
                <li><a href="http://localhost/library_final/add-books.php">Add New Book</a></li>
                <li><a href="http://localhost/library_final/book-management.php"></a></li>
                <li><a href="http://localhost/library_final/loan.php">Loaned Book</a></li>
                <li><a href="http://localhost/library_final/borrowers.php">View Borrowers</a></li>
            </ul>
        </nav>
</header>

<h1>Loaned Books</h1><br>

<main>
    <table>
        <thead>
            <tr>
                <th>Loan ID</th>
                <th>Book Title</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are any loaned books
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['loan_id']}</td>
                            <td>{$row['title']}</td>
                            <td>{$row['borrow_date']}</td>
                            <td>" . ($row['return_date'] ? $row['return_date'] : 'Not Returned') . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No books loaned by this borrower.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>
</body>
</html>
