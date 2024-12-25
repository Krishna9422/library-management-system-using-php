<?php
session_start();
include 'db.php';

if (!isset($_SESSION['borrower_id'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$borrower_id = $_SESSION['borrower_id'];

// Fetch borrower information
$sql = "SELECT borrower_name, borrower_email FROM borrowers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $borrower_id);
$stmt->execute();
$borrower_result = $stmt->get_result();
$borrower = $borrower_result->fetch_assoc();

// Fetch books that are currently loaned and not returned, along with the due date and fine
$sql = "
    SELECT 
        books.title, 
        loans.borrow_date,
        DATE_ADD(loans.borrow_date, INTERVAL 15 DAY) AS due_date,
        DATEDIFF(CURDATE(), DATE_ADD(loans.borrow_date, INTERVAL 15 DAY)) AS overdue_days
    FROM 
        loans
    JOIN 
        books ON loans.book_id = books.book_id
    WHERE 
        loans.borrower_id = ? 
        AND loans.return_date IS NULL
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $borrower_id);
$stmt->execute();
$books_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrower Dashboard</title>
    <link rel="stylesheet" href="./style/dashboard.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        h1,h2 {
            text-align: center;
            color: #333;
        }

        p {
            text-align: center;
            font-size: 1.1em;
            color: #555;
        }

        /* Dashboard Container */
        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        thead {
            background-color: violet;
            color: #fff;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        tbody tr:hover {
            background-color: violet;
        }

        th {
            font-weight: bold;
        }

        td {
            color: #333;
        }

        /* Button Styles */
        button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1em;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            margin-top: 10px;
            margin-left: 40%;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Message for No Books */
        .no-books-message {
            text-align: center;
            font-size: 1.1em;
            color: #777;
            padding: 20px;
        }
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4; 
        color: #333;
    }
    
   
   
header {
display: flex;
justify-content: space-between;
align-items: center;
padding: 10px 20px;
background-color: violet;
border-bottom: 1px solid #ddd;
}.logo {
width: 60px; /* Adjust logo size as necessary */
/* height: auto; */
display: block; /* Ensures it behaves like a block element */
margin: 5px /* Centers the logo */

}.logo img {
height: 50px; /* Adjust logo size as needed */
margin-right: 10px;
}

nav {
flex: 1;
display: flex;
justify-content: right;
}

nav ul.list {
display: flex;
list-style: none;
margin: 0;
padding: 0;
}

nav ul.list li {
margin: 0 15px;
}

nav ul.list li a {
text-decoration: none;
color: #333;
font-size: 18px;
font-weight: 600;
transition: color 0.3s;
}

nav ul.list li a:hover {
color: #007bff; /* Change color on hover */
}
    </style>
</head>
<body>
<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">

    <nav>
        <ul class="list">
            <li><a href="index.html">Home</a></li>
            <li><a href="http://localhost/library_final/book1.php">Books</a></li>
         
        </ul>
    </nav>
</header>
    <h1>Welcome, <?php echo htmlspecialchars($borrower['borrower_name']); ?></h1>
    <p>Email: <?php echo htmlspecialchars($borrower['borrower_email']); ?></p>
    
    <h2>Books Currently Loaned</h2>
    <?php if ($books_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Fine (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($book = $books_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['borrow_date']); ?></td>
                        <td><?php echo htmlspecialchars($book['due_date']); ?></td>
                        <td>
                            <?php 
                            $overdue_days = max(0, $book['overdue_days']); // Only count overdue days
                            $fine = $overdue_days * 2; // ₹2 per day
                            echo $fine > 0 ? "₹" . htmlspecialchars($fine) : "No Fine";
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-books-message">You have no books currently on loan.</p>
    <?php endif; ?>
</body>
</html>
