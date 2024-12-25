<?php
// Include database connection
include 'db.php';

// Initialize the SQL query
$sql = "
    SELECT DISTINCT
        b.id AS borrower_id,
        b.borrower_name,
        books.title AS book_title,
        books.isbn,
        l.borrow_date,
        DATE_ADD(l.borrow_date, INTERVAL 15 DAY) AS due_date,
        l.referral_code -- Include referral code
    FROM 
        borrowers b
    INNER JOIN 
        loans l ON b.id = l.borrower_id
    INNER JOIN 
        books ON l.book_id = books.book_id
";

// Check if a search term or date range is provided
$where_clauses = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(b.id LIKE '%$search%' OR b.borrower_name LIKE '%$search%' OR books.title LIKE '%$search%' OR books.isbn LIKE '%$search%' OR l.referral_code LIKE '%$search%')";
}

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = $conn->real_escape_string($_GET['start_date']);
    $where_clauses[] = "l.borrow_date >= '$start_date'";
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = $conn->real_escape_string($_GET['end_date']);
    $where_clauses[] = "l.borrow_date <= '$end_date'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY b.id ASC, l.borrow_date ASC"; // Order by borrower ID and borrow date

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowers and Books</title>
    <link rel="stylesheet" href="./style/books..css">
    <style>
      /* Style for the labels */
      h2 {
            text-align: center;
            color: #4A4A4A;
            margin-top: 20px;}
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
/* Style for the date input fields */
input[type="date"] {
    font-family: Arial, sans-serif;
    font-size: 16px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 200px; /* Set a fixed width */
    transition: border-color 0.3s;
}

/* Change border color on focus */
input[type="date"]:focus {
    border-color: #007BFF; /* Bootstrap primary color */
    outline: none; /* Remove default outline */
}

/* Style for the container */
.date-container {
    margin-bottom: 15px; /* Space between date inputs */
}


/* Optional: Style for the overall form */
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
</head>
<body>
<header>  
    <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    <nav>
        <ul class="list">
            <li><a href="http://localhost/library_final/home2.php">Home</a></li>
            <li><a href="http://localhost/library_final/books1.php">Books</a></li>
            <li><a href="http://localhost/library_final/return-book1.php">Return Books</a></li>
            <li><a href="http://localhost/library_final/loan_book1.php">Loan Book</a></li>
            <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                    <li><a href="http://localhost/library_final/fine1.php">Fine</a></li>
                    <li><a href="http://localhost/library_final/borrowers11.php">Borrower</a></li>
                    <li><a href="http://localhost/library_final/loan1.php">Loaned Book</a></li>
                    <li><a href="http://localhost/library_final/overduebook1.php">Overdue Books</a></li>   
                </ul>
            </li>
        </ul>
    </nav>
</header>

<h1>Borrowers and Borrowed Books</h1><br>

<!-- Search Form -->
<div class="search-container">
    <form method="GET" action="borrowers11.php">
        <input type="text" name="search" placeholder="Search by ID, Name, Title, ISBN, or Referral Code" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <br>
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" id="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" id="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
        <br>
        <button type="submit">Search</button>
    </form>
</div>

<main>
    <h2>Borrowers Information</h2><br>

    <table>
        <thead>
            <tr>
                <th>Borrower ID</th>
                <th>Borrower Name</th>
                <th>Book Title</th>
                <th>ISBN</th>
                <th>Referral Code</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are any results
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['borrower_id']}</td>
                            <td>{$row['borrower_name']}</td>
                            <td>{$row['book_title']}</td>
                            <td>{$row['isbn']}</td>
                            <td>{$row['referral_code']}</td>
                            <td>{$row['borrow_date']}</td>
                            <td>{$row['due_date']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No borrowers or borrowed books found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>

</body>
</html>
