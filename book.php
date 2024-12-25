<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List</title>
    <link rel="stylesheet" href="./style/books..css">
    <style>
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
</head>
<body>
<header>  
    <img src="https://www.mgmmcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    
    <nav>
        <ul class="list">
            <li><a href="index.html">Home</a></li>
            <li><a href="http://localhost/library_final/book.php">Books</a></li>
           
            <li class="dropdown">
                <a href="#"><b>Login</b></a>
                <ul class="dropdown-menu">
                    <li><a href="http://localhost/library_final/login.php">Student Login</a></li>
                    <li><a href="http://localhost/library_final/manager-login.php">Staff Login</a></li>
                    
                </ul>
            </li>
        </ul>
    </nav>
</header>

<h1>Books in the Library</h1>

<!-- Search Form -->
<div class="search-container">
    <form method="GET" action="http://localhost/library_final/book.php">
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
            // Prepare SQL query to fetch book details
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
                    $status = $row['available_copies'] > 0 ? 'Available' : 'Not Available';

                    echo "<tr>
                        <td>{$row['book_id']}</td>
                        <td>{$isbn}</td>
                        <td>{$row['title']}</td>
                        <td>{$row['author']}</td>
                        <td>{$status}</td>
                        <td>{$row['publishing_year']}</td>
                        <td>{$row['total_copies']}</td>
                        <td class='tooltip' data-isbn='{$isbn}'>{$row['loaned_copies']}</td>
                        <td>{$row['available_copies']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No books found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</main>



<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('borrowerModal');
    const modalClose = document.querySelector('.close');
    const borrowerList = document.getElementById('borrowerList');

    // Open modal on tooltip click
    document.querySelectorAll('.tooltip').forEach(item => {
        item.addEventListener('click', () => {
            const isbn = item.dataset.isbn;

            // Show loading state
            borrowerList.innerHTML = "<tr><td colspan='3'>Loading...</td></tr>";

            // Fetch borrower list
            fetch(`http://localhost/library_final/fetch_borrowers.php?isbn=${isbn}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        borrowerList.innerHTML = "<tr><td colspan='3'>No borrowers found</td></tr>";
                    } else {
                        borrowerList.innerHTML = data.map(borrower => `
                            <tr>
                                <td>${borrower.borrower_id}</td>
                                <td>${borrower.borrower_name}</td>
                                <td>${borrower.loan_date}</td>
                            </tr>
                        `).join('');
                    }
                    modal.style.display = 'block';
                })
                .catch(() => {
                    borrowerList.innerHTML = "<tr><td colspan='3'>Error fetching data</td></tr>";
                });
        });
    });

    // Close modal
    modalClose.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', event => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
