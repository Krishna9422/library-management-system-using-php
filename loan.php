<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - Loaned Books</title>
    <link rel="stylesheet" href="./style/books..css">
    <style>
        /* Floating box styling */
        .floating-box {
            background-color: #f3e0f3; /* Light purple background */
            padding: 15px;
            border-radius: 10px;
            position: absolute;
            display: none;
            width: 300px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .floating-box ul {
            list-style-type: none;
            padding-left: 0;
        }
        .floating-box li {
            margin: 10px 0;
        }
        .search-form {
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    margin: 20px ; /* Add space around the form */
    width: 100%; /* Ensure it spans the width of its container */
}





.search-form button:hover {
    background-color: grey; /* Darker blue on hover */
}

        .borrower-link {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
        }
        .search-form {
            margin-bottom: 20px;
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

<h1>Loaned Book</h1>
<!-- Search form -->
<form method="GET" action="" class="search-form">
    <input type="text" name="search" placeholder="Search by ISBN or Title" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <button type="submit">Search</button>
</form>

<!-- Table to display loaned books -->
<table border="1">
    <thead>
        <tr>
            <th>Book ID</th>
            <th>ISBN</th>
            <th>Title</th>
            <th>Author</th>
            <th>Loaned Copies</th>
            <th>Available Copies</th>
        </tr>
    </thead>
    <tbody>
        <?php
        include 'db.php'; // Include the database connection

        // Get search term
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

        // SQL query to get books with loaned copies only
        $sql = "
            SELECT 
                b.book_id, 
                b.isbn, 
                b.title, 
                b.author, 
                b.total_copies,
                COALESCE(l.loaned_copies, 0) AS loaned_copies,
                (b.total_copies - COALESCE(l.loaned_copies, 0)) AS available_copies
            FROM books b
            LEFT JOIN (
                SELECT isbn, COUNT(*) AS loaned_copies 
                FROM loans 
                GROUP BY isbn
            ) l ON b.isbn = l.isbn
            WHERE l.loaned_copies > 0
        ";

        // Add search condition if search term is provided
        if (!empty($search)) {
            $sql .= " AND (b.title LIKE '%$search%' OR b.isbn LIKE '%$search%')";
        }

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $isbn = $row['isbn'];
                $book_id = $row['book_id'];

                // Fetch borrower count
                $borrowerSql = "
                    SELECT COUNT(*) AS borrower_count 
                    FROM loans 
                    WHERE isbn = '$isbn' AND book_id = '$book_id'
                ";
                $borrowerResult = $conn->query($borrowerSql);
                $borrowerRow = $borrowerResult->fetch_assoc();
                $borrowerCount = $borrowerRow['borrower_count'];

                echo "<tr>
                    <td>{$row['book_id']}</td>
                    <td>{$isbn}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['author']}</td>
                    <td><span class='borrower-link' data-isbn='{$isbn}' data-book-id='{$book_id}'>{$borrowerCount}</span></td>
                    <td>{$row['available_copies']}</td>
                  </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No loaned books found</td></tr>";
        }
        ?>
    </tbody>
</table>

<!-- Floating Box to show borrower details -->
<div id="floating-box" class="floating-box">
    <h2>Borrower Details</h2>
    <ul id="borrower-list"></ul>
    <button id="close-box">Close</button>
</div>

<script>
// JavaScript functionality remains unchanged
document.addEventListener('DOMContentLoaded', () => {
    const borrowerLinks = document.querySelectorAll('.borrower-link');
    const floatingBox = document.getElementById('floating-box');
    const borrowerList = document.getElementById('borrower-list');

    borrowerLinks.forEach(link => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();

            const isbn = link.getAttribute('data-isbn');
            const bookId = link.getAttribute('data-book-id');

            const response = await fetch(`fetchbook.php?isbn=${isbn}&book_id=${bookId}`);
            const borrowers = await response.json();

            borrowerList.innerHTML = '';
            borrowers.forEach(borrower => {
                const listItem = document.createElement('li');
                const borrowDate = new Date(borrower.borrow_date).toLocaleDateString();
                listItem.textContent = `Borrower ID: ${borrower.borrower_id}, Name: ${borrower.borrower_name} (Referral Code: ${borrower.referral_code}, Borrowed on: ${borrowDate})`;
                borrowerList.appendChild(listItem);
            });

            const rect = link.getBoundingClientRect();
            floatingBox.style.left = `${rect.left}px`;
            floatingBox.style.top = `${rect.bottom + window.scrollY + 10}px`;
            floatingBox.style.display = 'block';
        });
    });

    document.getElementById('close-box').addEventListener('click', () => {
        floatingBox.style.display = 'none';
    });

    document.addEventListener('click', (e) => {
        if (!floatingBox.contains(e.target) && !e.target.classList.contains('borrower-link')) {
            floatingBox.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
