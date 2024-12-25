<?php
session_start();
include 'db.php'; // Database connection

// Current date
$current_date = date('Y-m-d');

// Query to fetch overdue loans
$sql = "
    SELECT 
        l.borrower_id, 
        b.title AS book_title, 
        l.referral_code, 
        l.due_date
    FROM loans l
    INNER JOIN books b ON l.book_id = b.book_id
    WHERE l.due_date < '$current_date'
";

$result = $conn->query($sql);

$overdueBooks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $overdueBooks[] = [
            'borrower_id' => $row['borrower_id'],
            'book_title' => $row['book_title'],
            'referral_code' => $row['referral_code'],
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
    <title>Overdue Books</title>
    <link rel="stylesheet" href="./style/return.css">
    <style>
        h2 {
            text-align: center;
            color: #4A4A4A;
            font-size: 24px;
            margin-top: 20px;
        }
        table {
            width: 80%;
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
        .list {
        list-style-type: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: space-around;
        align-items: center;
    }

    .list li {
        position: relative;
    }

    .list li a {
        text-decoration: none;
        color: white;
        font-size: 16px;
        font-weight: bold;
        padding: 8px 16px;
        display: block;
    }

    .list li a:hover {
        background-color: #fc25f8;
        border-radius: 5px;
    }
      .dropdown:hover.dropdown-menu {
        display: block;
    }

    .dropdown-menu {

        position: absolute;
        background-color: white;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        list-style: none;
        padding: 10px 0;
        min-width: 150px;
        top: 100%; /* Positions dropdown below the parent menu */
        left: 0;
    }

    .dropdown-menu li a {
        color: #333;
        padding: 10px 16px;
    }

    .dropdown-menu li a:hover {
        background-color: #f0f0f0;
        color: #6a11cb;
        border-radius: 5px;
    }
    .dropdown {
    position: relative; /* Ensure dropdown is positioned relative to its parent */
}

.dropdown:hover .dropdown-menu {
    display: block; /* Show dropdown on hover */
}

.dropdown-menu {
    display: none; /* Hide dropdown by default */
    position: absolute;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
    list-style: none;
    padding: 10px 0;
    min-width: 150px;
    top: 100%; /* Positions dropdown below the parent menu */
    left: 0;
}

.dropdown-menu li a {
    color: #333;
    padding: 10px 16px;
    text-decoration: none; /* Remove underline from links */
}

.dropdown-menu li a:hover {
    background-color: #f0f0f0;
    color: #6a11cb;
    border-radius: 5px;
}
    </style>
       <script>
    // Detect navigation type and redirect for back button
    window.addEventListener("pageshow", function (event) {
        if (event.persisted || performance.navigation.type === 2) {
            // Redirect to home.php if back button is pressed
            window.location.href = "http://localhost/library_final/home2.php";
        }
    });
</script>
<body>
    

<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul class="list">
                <li><a href="http://localhost/library_final/home2.php">Home</a></li>
                <li><a href="http://localhost/library_final/books1.php">Books</a></li>
             
                <li><a href="http://localhost/library_final/return-book1.php">Return  Books</a></li>
                <li><a href="http://localhost/library_final/loan_book1.php">Loan Book</a></li>
               
                <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                <li><a href="http://localhost/library_final/fine1.php">Fine</a></li>
                <li><a href="http://localhost/library_final/borrowers11.php">Borrower </a></li>
                <li><a href="http://localhost/library_final/loan1.php">Loaned Book</a></li>
                <li><a href="http://localhost/library_final/overduebook1.php">overdue books</a></li>   
                </ul>
            </li>
            </ul>
        </nav>
</header>


<h2>Overdue Books</h2>

<?php if (count($overdueBooks) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Borrower ID</th>
                <th>Book Title</th>
                <th>Referral Code</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($overdueBooks as $book): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['borrower_id']); ?></td>
                    <td><?php echo htmlspecialchars($book['book_title']); ?></td>
                    <td><?php echo htmlspecialchars($book['referral_code']); ?></td>
                    <td><?php echo htmlspecialchars($book['due_date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="no-overdue">No overdue books at this time.</div>
<?php endif; ?>

</body>
</html>
