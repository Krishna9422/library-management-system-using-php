<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan & Return Books</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="number"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border: none;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Loan & Return Books</h1>

    <!-- Loan Book Form -->
    <h2>Loan a Book</h2>
    <form method="POST" action="loan_book.php">
        <label for="borrower_id">Borrower ID:</label>
        <input type="number" name="borrower_id" required>

        <label for="book_id">Book ID:</label>
        <input type="number" name="book_id" required>

        <input type="submit" name="loan" value="Loan Book">
    </form>

    <!-- Return Book Form -->
    <h2>Return a Book</h2>
    <form method="POST" action="return_book.php">
        <label for="borrower_id_return">Borrower ID:</label>
        <input type="number" name="borrower_id" required>

        <label for="book_id_return">Book ID:</label>
        <input type="number" name="book_id" required>

        <input type="submit" name="return" value="Return Book">
    </form>
    
    <!-- Add any PHP error or success messages here -->
    <?php
    if (isset($_GET['message'])) {
        echo "<p style='color: green;'>" . htmlspecialchars($_GET['message']) . "</p>";
    }
    ?>
</body>
</html>
