
<?php
// Include PHPMailer and Database Configuration
require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/PHPMailer/src/SMTP.php';
require_once 'PHPMailer/PHPMailer/src/Exception.php';
include 'db.php';

// Function to send email using PHPMailer
function sendEmail($borrower_name, $borrower_email, $book_title, $due_date) {
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'Your@mail.com'; // Your Gmail address
    $mail->Password = 'password'; // Your Gmail app password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('Your@mail.com', 'Library Management');
    $mail->addAddress($borrower_email, $borrower_name);

    $mail->isHTML(true);
    $mail->Subject = "Loan Confirmation: $book_title";
    $mail->Body = "
        <h1>Book Loan Confirmation</h1>
        <p>Dear $borrower_name,</p>
        <p>You have successfully borrowed the book <strong>$book_title</strong>. The due date for returning this book is <strong>$due_date</strong>.</p>
        <p>Please ensure timely return to avoid fines.</p>
        <p>Thank you,</p>
        <p>Library Management</p>
    ";

    if ($mail->send()) {
        return true;
    } else {
        return "Mailer Error: " . $mail->ErrorInfo;
    }
}

// Handle Loan Processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $borrower_id = $_POST['borrower_id'];
    $title = $_POST['title'];
    $referral_code = $_POST['referral_code'];
    $borrow_date = $_POST['borrow_date'];
    $isbn = $_POST['isbn'];

    $borrowDateTime = new DateTime($borrow_date);
    $dueDateTime = $borrowDateTime->modify('+15 days');
    $due_date = $dueDateTime->format('Y-m-d');

    // Retrieve book_id and borrower details
    $bookQuery = "SELECT book_id FROM books WHERE isbn = ?";
    $stmt = $conn->prepare($bookQuery);
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $bookResult = $stmt->get_result();
    $book = $bookResult->fetch_assoc();

    $borrowerQuery = "SELECT borrower_name, borrower_email FROM borrowers WHERE id = ?";
    $stmt = $conn->prepare($borrowerQuery);
    $stmt->bind_param("i", $borrower_id);
    $stmt->execute();
    $borrowerResult = $stmt->get_result();
    $borrower = $borrowerResult->fetch_assoc();

    if ($book && $borrower) {
        $book_id = $book['book_id'];
        $borrower_name = $borrower['borrower_name'];
        $borrower_email = $borrower['borrower_email'];

        // Insert loan record
        $loanQuery = "INSERT INTO loans (borrower_id, book_id, referral_code, borrow_date, due_date, isbn) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($loanQuery);
        $stmt->bind_param("iissss", $borrower_id, $book_id, $referral_code, $borrow_date, $due_date, $isbn);

        if ($stmt->execute()) {
            // Update book's loaned_copies count
            $updateBookQuery = "UPDATE books SET loaned_copies = loaned_copies + 1 WHERE book_id = ?";
            $stmt = $conn->prepare($updateBookQuery);
            $stmt->bind_param("i", $book_id);
            $stmt->execute();

            // Send email notification
            $emailResult = sendEmail($borrower_name, $borrower_email, $title, $due_date);
            if ($emailResult === true) {
                $message = "Loan successfully processed and email sent!";
            } else {
                $message = "Loan processed but email could not be sent: $emailResult";
            }

            header("Location: loan_book1.php?message=" . urlencode($message));
            exit();
        } else {
            echo "Error: Could not save the loan record. " . $conn->error;
        }
    } else {
        echo "Error: Invalid borrower or book details.";
    }
}

// Fetch data for the form
$borrowerQuery = "SELECT id, borrower_name FROM borrowers";
$borrowerResult = $conn->query($borrowerQuery);

$titleQuery = "SELECT DISTINCT title FROM book_copies";
$titleResult = $conn->query($titleQuery);

$referralQuery = "
    SELECT title, referral_code, isbn
    FROM book_copies bc
    WHERE NOT EXISTS (
        SELECT 1 FROM loans l WHERE l.referral_code = bc.referral_code
    )
";
$referralResult = $conn->query($referralQuery);

// Prepare referral codes and ISBNs for JavaScript
$referralCodesByTitle = [];
while ($row = $referralResult->fetch_assoc()) {
    $referralCodesByTitle[$row['title']][] = [
        'referral_code' => $row['referral_code'],
        'isbn' => $row['isbn']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Book</title>
    <link rel="stylesheet" href="./style/loan.css">
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
}</style>
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

    <h1>Loan Book</h1>
    <form action="" method="POST">
        <label for="borrower_id">Select Borrower:</label>
        <select name="borrower_id" id="borrower_id" required>
            <option value="">Select Borrower</option>
            <?php while ($borrower = $borrowerResult->fetch_assoc()): ?>
                <option value="<?php echo $borrower['id']; ?>">
                    <?php echo $borrower['id'] . " - " . htmlspecialchars($borrower['borrower_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="title">Select Book Title:</label>
        <select name="title" id="title" required onchange="updateReferralCodes()">
            <option value="">Select Title</option>
            <?php while ($title = $titleResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($title['title']); ?>">
                    <?php echo htmlspecialchars($title['title']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="referral_code">Select Book (Referral Code):</label>
        <select name="referral_code" id="referral_code" required onchange="updateIsbn()">
            <option value="">Select Referral Code</option>
        </select>

        <label for="isbn">ISBN:</label>
        <input type="text" name="isbn" id="isbn" readonly>

        <input type="date" name="borrow_date" id="borrow_date" value="<?php echo date('Y-m-d'); ?>" 
       min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>


        <input type="submit" value="Loan Book">
    </form>

    <script>
        const referralCodesByTitle = <?php echo json_encode($referralCodesByTitle); ?>;

        function updateReferralCodes() {
            const titleSelect = document.getElementById("title");
            const referralCodeSelect = document.getElementById("referral_code");
            const isbnInput = document.getElementById("isbn");
            const selectedTitle = titleSelect.value;

            referralCodeSelect.innerHTML = '<option value="">Select Referral Code</option>';
            isbnInput.value = '';

            if (selectedTitle in referralCodesByTitle) {
                referralCodesByTitle[selectedTitle].forEach(item => {
                    const option = document.createElement("option");
                    option.value = item.referral_code;
                    option.textContent = item.referral_code;
                    option.dataset.isbn = item.isbn;
                    referralCodeSelect.appendChild(option);
                });
            }
        }

        function updateIsbn() {
            const referralCodeSelect = document.getElementById("referral_code");
            const isbnInput = document.getElementById("isbn");
            const selectedOption = referralCodeSelect.options[referralCodeSelect.selectedIndex];
            isbnInput.value = selectedOption.dataset.isbn || '';
        }
    </script>
</body>
</html>
```