<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$borrower_id = $_GET['borrower_id'];

// Fetch borrower details
$sql = "
    SELECT 
        br.borrower_name, 
        br.borrower_email, 
        SUM(DATEDIFF(CURDATE(), l.due_date) * 2) AS total_fine
    FROM loans l
    INNER JOIN borrowers br ON l.borrower_id = br.id
    WHERE l.borrower_id = '$borrower_id' AND l.due_date < CURDATE()
    GROUP BY l.borrower_id
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $borrower = $result->fetch_assoc();

    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'Your@mail.com';
        $mail->Password = 'app password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipient
        $mail->setFrom('Your@mail.com', 'Library Admin');
        $mail->addAddress($borrower['borrower_email'], $borrower['borrower_name']);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = 'Overdue Book Fine';
        $mail->Body = "
            <p>Dear {$borrower['borrower_name']},</p>
            <p>You have overdue books. Your total fine is ₹{$borrower['total_fine']}.</p>
            <p>Please return your books and settle the fines promptly.</p>
            <p>Regards,<br>Library Admin</p>
        ";

        $mail->send();
        echo "Email sent to {$borrower['borrower_name']}";
    } catch (Exception $e) {
        echo "Email could not be sent. Error: {$mail->ErrorInfo}";
    }
} else {
    echo "No overdue records for the borrower.";
}

$conn->close();
?>
