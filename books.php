<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List</title>
    <link rel="stylesheet" href="./style/books..css">
    <style>
        /* Tooltip styling */
        .tooltip .tooltiptext {
            visibility: hidden;
          
            background-color: #f9f9f9;
            color: #333;
            text-align: center;
            border-radius: 5px;
          
            position: absolute;
           
            
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.9em;
            box-shadow: 0px 0px 5px rgba(0,0,0,0.2);
        }

        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        nav {
        background-color: violet;
        padding: 10px;
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

<h1>Books in the Library</h1>

<!-- Search Form -->
<div class="search-container">
    <form method="GET" action="http://localhost/library_final/books.php">
        <input type="text" name="search" placeholder="Search by Title, ISBN, or Author" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit">Search</button>
    </form>
</div>

<main>
    <table>
        <thead>
            <tr>
                <th>Book ID</th>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Publishing Year</th>
                <th>Total Copies</th>
                <th>Loaned Copies</th>
                <th>Available Copies</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Prepare the main SQL query to get book information, including loaned copies count
            $sql = "
                SELECT 
                    b.book_id, 
                    b.isbn, 
                    b.title, 
                    b.author, 
                    b.publishing_year, 
                    b.total_copies,
                    COALESCE(l.loaned_copies, 0) AS loaned_copies,
                    (b.total_copies - COALESCE(l.loaned_copies, 0)) AS available_copies
                FROM books b
                LEFT JOIN (
                    SELECT isbn, COUNT(*) AS loaned_copies 
                    FROM loans 
                    GROUP BY isbn
                ) l ON b.isbn = l.isbn
            ";

            // Apply search filter if provided
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = $conn->real_escape_string($_GET['search']);
                $sql .= " WHERE b.isbn LIKE '%$search%' OR b.title LIKE '%$search%' OR b.author LIKE '%$search%'";
            }

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $isbn = $row['isbn'];

                    // Fetch referral codes from book_copies for Total Copies
                    $totalCopiesQuery = "SELECT referral_code FROM book_copies WHERE isbn = '$isbn'";
                    $totalCopiesResult = $conn->query($totalCopiesQuery);
                    $totalCopiesCodes = [];
                    while ($copyRow = $totalCopiesResult->fetch_assoc()) {
                        $totalCopiesCodes[] = $copyRow['referral_code'];
                    }

                    // Fetch referral codes from loans for Loaned Copies
                    $loanedCopiesQuery = "SELECT referral_code FROM loans WHERE isbn = '$isbn'";
                    $loanedCopiesResult = $conn->query($loanedCopiesQuery);
                    $loanedCopiesCodes = [];
                    while ($loanRow = $loanedCopiesResult->fetch_assoc()) {
                        $loanedCopiesCodes[] = $loanRow['referral_code'];
                    }

                    // Calculate available referral codes for Available Copies
                    $availableCodes = array_diff($totalCopiesCodes, $loanedCopiesCodes);

                    $status = $row['available_copies'] > 0 ? 'Available' : 'Not Available';

                    echo "<tr>
                        <td>{$row['book_id']}</td>
                        <td>{$isbn}</td>
                        <td>{$row['title']}</td>
                        <td>{$row['author']}</td>
                        <td>{$status}</td>
                        <td>{$row['publishing_year']}</td>
                        <td class='tooltip'>{$row['total_copies']}
                            <span class='tooltiptext'>Referral Codes: " . implode(', ', $totalCopiesCodes) . "</span>
                        </td>
                        <td class='tooltip'>{$row['loaned_copies']}
                            <span class='tooltiptext'>Referral Codes: " . implode(', ', $loanedCopiesCodes) . "</span>
                        </td>
                        <td class='tooltip'>{$row['available_copies']}
                            <span class='tooltiptext'>Referral Codes: " . implode(', ', $availableCodes) . "</span>
                        </td>
                      </tr>";
                }
            } else {
                echo "<tr><td colspan='10'>No books found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>
</body>
</html>
