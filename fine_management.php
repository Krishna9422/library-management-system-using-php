<?php
// Include the necessary PHPMailer files manually
require_once 'PHPMailer/PHPMailer/src/PHPMailer.php';  // PHPMailer class
require_once 'PHPMailer/PHPMailer/src/SMTP.php';       // SMTP class
require_once 'PHPMailer/PHPMailer/src/Exception.php';  // PHPMailer Exception class

// Database connection
include 'db.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $borrower_id = mysqli_real_escape_string($conn, $_POST['borrower_id']);
    $upi_reference = mysqli_real_escape_string($conn, $_POST['upi_reference']);
    $fine_amount = mysqli_real_escape_string($conn, $_POST['fine_amount']);

    // Check if the borrower has a fine
    $query = "SELECT * FROM fine WHERE borrower_id = ? AND fine_status = 'Unpaid' LIMIT 1";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $borrower_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update the fine status to 'Paid'
            $update_query = "UPDATE fine SET fine_status = 'Paid', upi_reference = ?, fine_paid = ? WHERE borrower_id = ?";
            if ($stmt_update = $conn->prepare($update_query)) {
                $stmt_update->bind_param("sdi", $upi_reference, $fine_amount, $borrower_id);
                $stmt_update->execute();
                
                // Send confirmation email to the borrower
                $email_query = "SELECT borrower_email FROM borrowers WHERE borrower_id = ?";
                if ($stmt_email = $conn->prepare($email_query)) {
                    $stmt_email->bind_param("i", $borrower_id);
                    $stmt_email->execute();
                    $stmt_email->bind_result($borrower_email);
                    $stmt_email->fetch();
                    
                    // Send email
                    if ($borrower_email) {
                        // Create a new PHPMailer instance
                        $mail = new PHPMailer\PHPMailer\PHPMailer();
                        try {
                            // Set mailer to use SMTP
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                            $mail->SMTPAuth = true;
                            $mail->Username = 'Your@mail.com'; // Replace with your email
                            $mail->Password = 'app password '; // Replace with your email password
                            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;

                            // Recipients
                            $mail->setFrom('Your@mail.com', 'Library Management System');
                            $mail->addAddress($borrower_email); // Borrower's email

                            // Content
                            $mail->isHTML(true);
                            $mail->Subject = 'Fine Payment Confirmation';
                            $mail->Body    = "
                                <p>Dear Borrower,</p>
                                <p>Your fine payment of INR $fine_amount has been successfully received. Thank you for clearing your dues.</p>
                                <p>Transaction Reference: $upi_reference</p>
                                <p>If you have any questions, feel free to contact us.</p>
                                <p>Regards,<br>Library Management System</p>
                            ";

                            // Send email
                            if ($mail->send()) {
                                $message = "Fine payment received successfully, and confirmation email sent to borrower.";
                            } else {
                                $message = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
                            }
                        } catch (Exception $e) {
                            $message = "Error sending email: " . $mail->ErrorInfo;
                        }
                    }
                }
            }
        } else {
            $message = "No unpaid fine found for the borrower.";
        }
    }
}

$conn->close();
?>

<!-- HTML Form for Fine Payment -->
<html>
<body>
    <form action="fine_management.php" method="POST">
        <label for="borrower_id">Borrower ID:</label>
        <input type="text" id="borrower_id" name="borrower_id" required>

        <label for="upi_reference">UPI Reference:</label>
        <input type="text" id="upi_reference" name="upi_reference" required>

        <label for="fine_amount">Fine Amount:</label>
        <input type="number" id="fine_amount" name="fine_amount" required>

        <input type="submit" value="Pay Fine">
    </form>
    <p><?php echo $message; ?></p>
</body>
</html>
