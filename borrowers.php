<?php
// Include database connection
include 'db.php';

// Initialize the SQL query with LEFT JOIN to include all borrowers
$sql = "
    SELECT 
        b.id AS borrower_id,
        b.borrower_name,
        b.borrower_email,
        COUNT(DISTINCT l.book_id) AS book_count,  -- Counting distinct books
        IFNULL(
            GROUP_CONCAT(DISTINCT CONCAT(books.title, ' (ISBN: ', books.isbn, ')') SEPARATOR ', '),
            'No books loaned'
        ) AS book_details
    FROM 
        borrowers b
    LEFT JOIN 
        loans l ON b.id = l.borrower_id
    LEFT JOIN 
        books ON l.book_id = books.book_id
";

// Check if a search term is provided
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql .= " WHERE b.id LIKE '%$search%' OR b.borrower_name LIKE '%$search%' OR b.borrower_email LIKE '%$search%'";
}

$sql .= "
    GROUP BY 
        b.id
    ORDER BY 
        b.id ASC  -- Arrange borrower ID in ascending order
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowers List</title>
    <link rel="stylesheet" href="./style/books..css">
    <style>
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
        h2 {
            text-align: center;
            margin-top: 30px;
        }
        /* Tooltip styling */
        .tooltip {
            position: relative;
            cursor: pointer;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            background-color: #f9f9f9;
            color: #333;
            text-align: center;
            border-radius: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -60px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.9em;
            box-shadow: 0px 0px 5px rgba(0,0,0,0.2);
            width: 200px;
            padding: 5px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        /* Style for the Search Form */
        .search-container {
            text-align: center;
            margin: 20px 0;
        }

        /* Table styling */
        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
    <script>
        // Detect navigation type and redirect for back button
        window.addEventListener("pageshow", function (event) {
            if (event.persisted || performance.navigation.type === 2) {
                // Redirect to home.php if back button is pressed
                window.location.href = "http://localhost/library_final/home.php";
            }
        });
    </script>
</head>
<body>
<header>  
<img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
        <nav>
            <ul class="list">
                <li><a href="http://localhost/library_final/home.php">Home</a></li>
                <li><a href="http://localhost/library_final/books.php">Books</a></li>
               
                <li class="dropdown">
                <a href="#"><b>Add staff </b></a>
                <ul class="dropdown-menu">
                <li><a href="http://localhost/library_final/admin.php">Request</a></li>
                <li><a href="http://localhost/library_final/signup.php">Manually </a></li>
                </ul>
            </li>
                <li><a href="http://localhost/library_final/add-book.php">Add New Book</a></li>
                <li><a href="http://localhost/library_final/return-book.php">Return  Books</a></li>
                <li><a href="http://localhost/library_final/loan_book.php">Loan Book</a></li>
               
                <li><a href="http://localhost/library_final/borrowers.php">Student Id</a></li>
              
               
               
                <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                <li><a href="http://localhost/library_final/fine.php">Fine</a></li>
                <li><a href="http://localhost/library_final/borrowers1.php">Borrower </a></li>
                <li><a href="http://localhost/library_final/loan.php">Loaned Book</a></li>
                <li><a href="http://localhost/library_final/overduebook.php">overdue books</a></li>   
                </ul>
            </li>
            </ul>
        </nav>
</header>

<h1>Student List</h1><br>

<!-- Search Form -->
<div class="search-container">
    <form method="GET" action="borrowers.php">
        <input type="text" name="search" placeholder="Search by ID, Name, or Email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit">Search</button>
    </form>
</div>

<main>
    <h2>Borrowers Information</h2><br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Books Loaned</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are any borrowers
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['borrower_id']}</td>
                            <td>{$row['borrower_name']}</td>
                            <td>{$row['borrower_email']}</td>
                            <td class='tooltip'>{$row['book_count']}
                                <span class='tooltiptext'>Loaned Books: {$row['book_details']}</span>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No borrowers found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>

</body>
</html>
