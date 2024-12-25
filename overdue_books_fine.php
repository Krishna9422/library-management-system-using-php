<?php
session_start();
include 'db.php'; // Database connection

// Current date
$current_date = date('Y-m-d');

// Create a table to store overdue book fines (only if it doesn't exist already)
$create_table_sql = "
    CREATE TABLE IF NOT EXISTS overdue_fines (
        id INT AUTO_INCREMENT PRIMARY KEY,
        borrower_id INT,
        borrower_name VARCHAR(255),
        borrower_email VARCHAR(255),
        book_id INT,
        book_title VARCHAR(255),
        referral_code VARCHAR(50),
        due_date DATE,
        overdue_days INT,
        fine_amount DECIMAL(10, 2),
        fine_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (borrower_id) REFERENCES borrowers(id),
        FOREIGN KEY (book_id) REFERENCES books(book_id)
    );
";
$conn->query($create_table_sql);

// Query to fetch overdue loans with fine calculation and borrower names
$sql = "
    SELECT 
        l.borrower_id, 
        br.borrower_name,
        br.borrower_email,
        b.book_id,
        b.title AS book_title, 
        l.referral_code, 
        l.due_date,
        DATEDIFF('$current_date', l.due_date) AS overdue_days -- Calculate overdue days
    FROM loans l
    INNER JOIN books b ON l.book_id = b.book_id
    INNER JOIN borrowers br ON l.borrower_id = br.id
    WHERE l.due_date < '$current_date'
";

$result = $conn->query($sql);

$borrowerFines = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $borrower_id = $row['borrower_id'];
        $borrower_name = $row['borrower_name'];
        $borrower_email = $row['borrower_email'];
        $book_id = $row['book_id'];
        $book_title = $row['book_title'];
        $referral_code = $row['referral_code'];
        $due_date = $row['due_date'];
        $overdue_days = $row['overdue_days'];
        $fine = $overdue_days * 2; // Fine: ₹2 per day

        // Insert the fine details into the overdue_fines table
        $insert_sql = "
            INSERT INTO overdue_fines (borrower_id, borrower_name, borrower_email, book_id, book_title, referral_code, due_date, overdue_days, fine_amount)
            VALUES ('$borrower_id', '$borrower_name', '$borrower_email', '$book_id', '$book_title', '$referral_code', '$due_date', '$overdue_days', '$fine')
        ";
        $conn->query($insert_sql);
    }
}

// Fetch the overdue fines from the newly created table for display
$fetch_fines_sql = "SELECT * FROM overdue_fines";
$overdue_fines_result = $conn->query($fetch_fines_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Books and Fines</title>
    <link rel="stylesheet" href="./style/books..css">
    <style>
        h2 {
            text-align: center;
            color: #4A4A4A;
            margin-top: 20px;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: violet;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .no-overdue {
            text-align: center;
            font-size: 18px;
            color: green;
        }
    </style>
</head>
<body>

<h2>Overdue Books and Fines</h2>

<?php if ($overdue_fines_result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Borrower ID</th>
                <th>Borrower Name</th>
                <th>Book Title</th>
                <th>Due Date</th>
                <th>Overdue Days</th>
                <th>Fine Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $overdue_fines_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['borrower_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['borrower_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['overdue_days']); ?></td>
                    <td>₹<?php echo htmlspecialchars($row['fine_amount']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-overdue">No overdue books at the moment!</p>
<?php endif; ?>

</body>
</html>
