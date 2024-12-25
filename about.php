<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Library Management System</title>
    <link rel="stylesheet" href="./style/home.css">
    <style>
             } 
      .hero h1{
        color: voilet;
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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: violet;
            padding: 10px;
        }
        .logo {
            width: 100px;
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
        .list li a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 8px 16px;
            display: block;
        }
        .hero {
            background-image: url("./pexels-cottonbro-6344231.jpg");
            padding: 50px;
            color: white;
            text-align: center;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        footer {
            background-color: violet;
            text-align: center;
            padding: 10px;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
<header>  
    <img src="https://www.mgmlcha.org/images/logos/mgmlogo.png" alt="College Logo" class="logo">
    <nav>
        <ul class="list">
            <li><a href="http://localhost/library_final/home.php">Home</a></li>
            <li><a href="http://localhost/library_final/books.php">Books</a></li>
            <li><a href="http://localhost/library_final/add-book.php">Add New Book</a></li>
            <li><a href="http://localhost/library_final/return-book.php">Return Books</a></li>
            <li><a href="http://localhost/library_final/loan_book.php">Loan Book</a></li>
            <li><a href="http://localhost/library_final/borrowers.php">Student Id</a></li>
            <li class="dropdown">
                <a href="#"><b>Loan menu</b></a>
                <ul class="dropdown-menu">
                    <li><a href="http://localhost/library_final/fine.php">Fine</a></li>
                    <li><a href="http://localhost/library_final/borrowers1.php">Borrower</a></li>
                    <li><a href="http://localhost/library_final/loan.php">Loaned Book</a></li>
                    <li><a href="http://localhost/library_final/overduebook.php">Overdue Books</a></li>   
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <section class="hero">
        <h1>About Us</h1>
    </section>

    <div class="content">
        <h2>Welcome to Our Library Management System</h2>
        <p>Our Library Management System is designed to streamline the management of library resources, ensuring that both staff and users can access and manage books efficiently.</p>
        
        <h3>Our Mission</h3>
        <p>We aim to provide a user-friendly platform that enhances the library experience, making it easier for users to find, borrow, and return books.</p>

        <h3>Our Features</h3>
        <ul>
            <li>Comprehensive Book Management</li>
            <li>User-Friendly Interface for Borrowers</li>
            <li>Real-Time Loan Tracking</li>
            <li>Efficient Fine Management System</li>
        </ul>

        <h3>Get in Touch</h3>
        <p>If you have any questions or feedback, feel free to reach out to us!</p>
    </div>
</main>

<footer>
    <p>Library Management System &copy; 2023</p>
</footer>
</body>
</html>