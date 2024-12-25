<?php
include 'db.php'; // Database connection

// Current date
$current_date = date('Y-m-d');

// Query to fetch overdue loans
$sql = "
    SELECT 
        l.borrower_id, 
        l.isbn, 
        l.due_date, 
        b.title, 
        br.borrower_name, 
        br.borrower_email
    FROM loans l
    INNER JOIN books b ON l.book_id = b.book_id
    INNER JOIN borrowers br ON l.borrower_id = br.id
    WHERE l.due_date < '$current_date'
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Books</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .no-records {
            text-align: center;
            font-size: 18px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Overdue Books</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Borrower Name</th>
                    <th>Email</th>
                    <th>Book Title</th>
                    <th>ISBN</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['borrower_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['borrower_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-records">No overdue books found.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
