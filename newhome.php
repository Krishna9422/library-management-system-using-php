<?php
// Start the session
session_start();

// Check if the session exists, and if a 'logout' parameter is present, destroy the session
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: homee2.html');
    exit();
}

// Prevent access to this page if the session is not active
if (!isset($_SESSION['user_id'])) { // Replace 'user_id' with your session variable
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="./style/home.css">
    <style>
        .hero {
            background-image: url("./pexels-cottonbro-6344231.jpg");
        }
        .hero h1 {
            color: violet;
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
            border-radius: 5px;
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
            top: 100%;
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
            position: relative;
        }
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .dropdown-menu {
            display: none;
        }
    </style>
    <script>
        // Prevent accessing this page after logout using the back button
        document.addEventListener('DOMContentLoaded', () => {
            if (!sessionStorage.getItem('loggedIn')) {
                window.location.href = 'index.html';
            }

            // Clear sessionStorage on logout
            const logoutLinks = document.querySelectorAll('a[href*="logout=true"]');
            logoutLinks.forEach(link => {
                link.addEventListener('click', () => {
                    sessionStorage.removeItem('loggedIn');
                });
            });
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
            <li><a href="http://localhost/library_final/add-book.php">Add New Book</a></li>
            <li><a href="http://localhost/library_final/return-book.php">Return Books</a></li>
            <li><a href="http://localhost/library_final/loan_book.php">Loan Book</a></li>
            <li><a href="http://localhost/library_final/borrowers.php">Student Id</a></li>
            <li class="dropdown">
                <a href="#"><b>Loan Menu</b></a>
                <ul class="dropdown-menu">
                    <li><a href="http://localhost/library_final/fine.php">Fine</a></li>
                    <li><a href="http://localhost/library_final/borrowers1.php">Borrower</a></li>
                    <li><a href="http://localhost/library_final/loan.php">Loaned Book</a></li>
                    <li><a href="http://localhost/library_final/overduebook.php">Overdue Books</a></li>
                </ul>
            </li>
            <!-- Logout button -->
            <li><a href="http://localhost/library_final/home.php?logout=true">Logout</a></li>
        </ul>
    </nav>
</header>
<main>
    <section class="hero">
        <h1>Manage Your Library Efficiently</h1><br><br>
        <p>Welcome to the Library Management System.<br><br>Here, you can manage books, keep track of loans, and ensure the library is well-organized. Use the navigation links to get started.</p>
    </section>
    <section class="features">
        <h2>Features of Our Library Management System</h2>
        <ul>
            <li><i class="fas fa-book"></i> Book Management: Easily add, edit, and delete books from our catalog.</li>
            <li><i class="fas fa-user"></i> User Management: Manage student and staff accounts, including login credentials and permissions.</li>
            <li><i class="fas fa-calendar"></i> Loan Management: Track book loans, due dates, and fines.</li>
            <li><i class="fas fa-search"></i> Search Functionality: Quickly find books by title, author, or subject.</li>
        </ul>
    </section>
    <section class="stats">
        <h2>Library Statistics</h2>
        <ul>
            <li><i class="fas fa-book"></i> Total Books: 10,000+</li>
            <li><i class="fas fa-user"></i> Registered Users: 5,000+</li>
            <li><i class="fas fa-calendar"></i> Average Daily Loans: 500+</li>
        </ul>
    </section>
</main>
<footer>
    <p>Library Management</p>
</footer>
</body>
</html>
