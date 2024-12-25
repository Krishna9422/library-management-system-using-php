<?php
session_start();
include 'db.php'; // Database connection

// Current date
$current_date = date('Y-m-d');

// Query to fetch overdue loans with fine calculation and borrower names
$sql = "
    SELECT 
        l.borrower_id, 
        br.borrower_name,
        br.borrower_email,
        b.title AS book_title, 
        l.referral_code, 
        l.due_date,
        DATEDIFF('$current_date', l.due_date) AS overdue_days -- Calculate overdue days
    FROM loans l
    INNER JOIN books b ON l.book_id = b.book_id
    INNER JOIN borrowers br ON l.borrower_id = br.id
    WHERE l.due_date < '$current_date'
";

$result = $conn->query($sql);

$borrowerFines = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $borrower_id = $row['borrower_id'];
        $borrower_name = $row['borrower_name'];
        $borrower_email = $row['borrower_email'];
        $book_info = [
            'book_title' => $row['book_title'],
            'referral_code' => $row['referral_code'],
            'due_date' => $row['due_date'],
            'fine' => $row['overdue_days'] * 2, // Fine: ₹2 per day
            'overdue_days' => $row['overdue_days'] // Store overdue days for later use
        ];

        // Group books by borrower_id
        if (!isset($borrowerFines[$borrower_id])) {
            $borrowerFines[$borrower_id] = [
                'borrower_name' => $borrower_name,
                'borrower_email' => $borrower_email,
                'total_fine' => 0,
                'books' => []
            ];
        }

        $borrowerFines[$borrower_id]['total_fine'] += $book_info['fine'];
        $borrowerFines[$borrower_id]['books'][] = $book_info;
    }
}

// Encode borrower fines into JSON for JavaScript
$borrowerFinesJson = json_encode($borrowerFines);

$conn->close();
?>

<?php
// PHPMailer Configuration for sending email
require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php'; 

// Function to send email
function sendEmail($borrower_name, $borrower_email, $book_title, $due_date, $overdue_days, $fine) {
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'Your@mail.com'; // Your Gmail address
    $mail->Password = 'password'; // Your Gmail app password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
    $mail->Port = 587;

    $subject = "Overdue Book Notification";
    $message = "
        Dear $borrower_name,

        This is a reminder that the book '$book_title' is overdue.

        Due Date: $due_date
        Overdue Days: $overdue_days
        Fine Amount: ₹$fine

        Please return the book as soon as possible to avoid further fines.

        Thank you,
        Library Management
    ";

    $mail->setFrom('Your@mail.com', 'Library Management');
    $mail->addAddress($borrower_email, $borrower_name);
    $mail->Subject = $subject;
    $mail->Body = $message;

    if($mail->send()) {
        return "success";
    } else {
        return "error: " . $mail->ErrorInfo;
    }
}

// Check if the "Send Email to All" button is clicked
$emailStatus = '';
if (isset($_POST['send_email_all'])) {
    foreach ($borrowerFines as $borrower_id => $details) {
        foreach ($details['books'] as $book) {
            // Send email to each borrower and book
            $emailStatus = sendEmail($details['borrower_name'], $details['borrower_email'], $book['book_title'], $book['due_date'], $book['overdue_days'], $book['fine']);
            if ($emailStatus == "success") {
                echo "Email sent successfully to " . $details['borrower_email'] . "<br>";
            } else {
                echo "Failed to send email to " . $details['borrower_email'] . ". Error: " . $emailStatus . "<br>";
            }
        }
    }
}

// Check if the "Send Email" button is clicked for a specific borrower
if (isset($_POST['send_email']) && isset($_POST['borrower_id'])) {
    $borrower_id = $_POST['borrower_id'];
    if (isset($borrowerFines[$borrower_id])) {
        $details = $borrowerFines[$borrower_id];
        foreach ($details['books'] as $book) {
            // Call sendEmail for this borrower and book
            $emailStatus = sendEmail($details['borrower_name'], $details['borrower_email'], $book['book_title'], $book['due_date'], $book['overdue_days'], $book['fine']);
            if ($emailStatus == "success") {
                echo "Email sent successfully to " . $details['borrower_email'] . "<br>";
            } else {
                echo "Failed to send email to " . $details['borrower_email'] . ". Error: " . $emailStatus . "<br>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Books and Fines</title>
    <link rel="stylesheet" href="./style/books..css">
    <style>
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
        body {
            font-family: Arial, sans-serif;
        }
        h2 {
            text-align: center;
            color: #4A4A4A;
            font-size: 24px;
            margin-top: 20px;
        }
        table {
            width: 90%;
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
        .btn-primary {
    background-color: purple; /* Changed to purple */
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.btn-primary:hover {
    background-color: darkviolet; /* Darker shade for hover effect */
}

.btn-send-all {
    background-color: purple; /* Changed to purple */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-bottom: 20px;
}
.btn-send-all:hover {
    background-color: darkviolet; /* Darker shade for hover effect */
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
                    <li><a href="http://localhost/library_final/Fine1.php">Fine</a></li>
                    <li><a href="http://localhost/library_final/borrowers11.php">Borrower</a></li>
                    <li><a href="http://localhost/library_final/loan1.php">Loaned Book</a></li>
                    <li><a href="http://localhost/library_final/overduebook1.php">Overdue Books</a></li>   
                </ul>
            </li>
        </ul>
    </nav>
</header>

<h2>Overdue Books and Fines</h2>

<!-- Button to send email to all borrowers -->
<div style="text-align: center; margin: 20px 0;">
<form method="POST">
    <button type="submit" name="send_email_all" class="btn-send-all">Send Email to All</button>
</form>
<div>
<?php if (count($borrowerFines) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Borrower ID</th>
                <th>Borrower Name</th>
                <th>Books Overdue (Details)</th>
                <th>Total Fine (₹)</th>
                <th>Send Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($borrowerFines as $borrower_id => $details): ?>
                <tr>
                    <td><?php echo htmlspecialchars($borrower_id); ?></td>
                    <td><?php echo htmlspecialchars($details['borrower_name']); ?></td>
                    <td>
                        <ul>
                            <?php foreach ($details['books'] as $book): ?>
                                <li><?php echo htmlspecialchars($book['book_title']) . " (Fine: ₹" . $book['fine'] . ")"; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>₹<?php echo htmlspecialchars($details['total_fine']); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="borrower_id" value="<?php echo htmlspecialchars($borrower_id); ?>">
                            <button type="submit" name="send_email" class="btn-primary">Send Email</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-overdue">No overdue books at the moment!</p>
<?php endif; ?>

</body>
</html>
