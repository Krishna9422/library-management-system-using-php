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

$overdueBooks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $overdueBooks[] = [
            'borrower_name' => $row['borrower_name'],
            'borrower_email' => $row['borrower_email'],
            'title' => $row['title'],
            'isbn' => $row['isbn'],
            'due_date' => $row['due_date']
        ];
    }
}

// Encode overdue books into JSON for JavaScript
$overdueBooksJson = json_encode($overdueBooks);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Books Alert</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>Overdue Books</h1>

    <script>
        // Parse overdue books from PHP JSON
        const overdueBooks = <?php echo $overdueBooksJson; ?>;

        if (overdueBooks.length > 0) {
            overdueBooks.forEach(book => {
                Swal.fire({
                    title: 'Overdue Book Alert!',
                    html: `
                        <strong>Borrower:</strong> ${book.borrower_name} <br>
                        <strong>Email:</strong> ${book.borrower_email} <br>
                        <strong>Book Title:</strong> ${book.title} <br>
                        <strong>ISBN:</strong> ${book.isbn} <br>
                        <strong>Due Date:</strong> ${book.due_date}`,
                    icon: 'warning',
                    confirmButtonText: 'Okay',
                    timer: 10000 // Alert will auto-close after 10 seconds
                });
            });

            // Redirect to another page after alerts
            setTimeout(() => {
                window.location.href = 'home.php';
            }, 15000); // Adjust timing as needed (15 seconds here)
        } else {
            Swal.fire({
                title: 'No Overdue Books',
                text: 'All books are returned on time.',
                icon: 'success',
                confirmButtonText: 'Great!'
            }).then(() => {
                // Redirect immediately for no overdue books
                window.location.href = 'http://localhost/library_final/home.php';
            });
        }
    </script>
</body>
</html>
