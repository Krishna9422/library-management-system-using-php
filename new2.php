<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Loans and Fine Payment</title>
</head>
<body>
    <h2>All Loans and Fine Payment</h2>

    <!-- Form to input borrower_id with a dropdown showing all borrower IDs -->
    <form method="POST">
        <label for="borrower_id">Select Borrower ID:</label>
        <select id="borrower_id" name="borrower_id" required>
            <option value="">Select Borrower ID</option>
            <?php
            include 'db.php';

            // Fetch all borrower IDs from the loans table
            $sql = "SELECT DISTINCT borrower_id FROM loans";
            $result = $conn->query($sql);

            // Populate the dropdown with borrower IDs
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['borrower_id'] . '">' . $row['borrower_id'] . '</option>';
            }
            ?>
        </select>
        <button type="submit">Show Loans</button>
    </form>

    <br><br>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrower_id'])) {
        // Get borrower_id from the form
        $borrower_id = intval($_POST['borrower_id']);
        $current_date = date('Y-m-d');

        // Fetch all books borrowed by the borrower
        $sql = "SELECT loans.id, loans.borrower_id, loans.book_id, loans.due_date, books.title, loans.referral_code, loans.isbn
                FROM loans
                JOIN books ON loans.book_id = books.book_id
                WHERE loans.borrower_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $borrower_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<h3>Loan Details for Borrower ID: $borrower_id</h3>";
            echo "<table border='1'>
                    <tr>
                        <th>Borrower ID</th>
                        <th>Book Title</th>
                        <th>Due Date</th>
                        <th>Referral Code</th>
                        <th>ISBN</th>
                        <th>Fine</th>
                        <th>Return</th>
                    </tr>";

            while ($row = $result->fetch_assoc()) {
                $book_id = $row['book_id'];
                $due_date = $row['due_date'];
                $days_overdue = ceil((strtotime($current_date) - strtotime($due_date)) / (60 * 60 * 24));
                $fine_amount = $days_overdue > 0 ? $days_overdue * 2 : 0; // Fine is calculated if overdue

                echo "<tr>
                        <td>{$row['borrower_id']}</td>
                        <td>{$row['title']}</td>
                        <td>{$due_date}</td>
                        <td>{$row['referral_code']}</td>
                        <td>{$row['isbn']}</td>
                        <td>{$fine_amount} INR</td>";

                // Add form for returning books
                echo "<td>
                        <form method='POST' action=''>
                            <input type='hidden' name='loan_id' value='{$row['id']}'>
                            <input type='hidden' name='borrower_id' value='{$borrower_id}'>
                            <select name='referral_code' id='referral_code_{$row['id']}'>
                                <option value=''>Select Referral Code</option>
                            </select>
                            <button type='submit' name='return_book'>Return Book</button>
                        </form>
                      </td>
                    </tr>";

                // Fetch available referral codes for the book
                ?>
                <script>
                    // Use AJAX to dynamically populate the referral codes based on selected book
                    document.getElementById('referral_code_<?= $row['id'] ?>').addEventListener('focus', function() {
                        var book_id = <?= $book_id ?>;
                        var referral_select = this;

                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'get_referral_codes.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                var options = JSON.parse(xhr.responseText);
                                options.forEach(function(option) {
                                    var opt = document.createElement('option');
                                    opt.value = option.referral_code;
                                    opt.textContent = option.referral_code;
                                    referral_select.appendChild(opt);
                                });
                            }
                        };
                        xhr.send('book_id=' + book_id);
                    });
                </script>
                <?php
            }

            echo "</table>";
        } else {
            echo "<p>No loans found for this borrower.</p>";
        }

        // Process return and fine payment
        if (isset($_POST['return_book'])) {
            $loan_id = intval($_POST['loan_id']);
            $borrower_id = intval($_POST['borrower_id']);
            $return_date = date('Y-m-d');
            $referral_code = $_POST['referral_code'];

            // Fetch the loan details to check fine
            $loan_sql = "SELECT * FROM loans WHERE id = ?";
            $loan_stmt = $conn->prepare($loan_sql);
            $loan_stmt->bind_param("i", $loan_id);
            $loan_stmt->execute();
            $loan_result = $loan_stmt->get_result();
            $loan_data = $loan_result->fetch_assoc();

            // If the book is overdue, calculate and insert fine into the fine1 table
            if ($loan_data['due_date'] < $return_date) {
                $days_overdue = ceil((strtotime($return_date) - strtotime($loan_data['due_date'])) / (60 * 60 * 24));
                $fine_amount = $days_overdue * 2;

                // Insert fine details into the fine1 table
                $fine_sql = "INSERT INTO fine1 (borrower_id, overdue_fine, fine_date) 
                             VALUES (?, ?, ?)";
                $fine_stmt = $conn->prepare($fine_sql);
                $fine_stmt->bind_param("iis", $borrower_id, $fine_amount, $return_date);
                $fine_stmt->execute();
            }

            // Insert into return table
            $return_sql = "INSERT INTO return (borrower_id, book_id, referral_code, isbn, return_date, borrow_date) 
                           SELECT borrower_id, book_id, referral_code, isbn, ?, borrow_date 
                           FROM loans WHERE id = ?";
            $return_stmt = $conn->prepare($return_sql);
            $return_stmt->bind_param("si", $return_date, $loan_id);
            $return_stmt->execute();

            // Remove the book from loans table
            $delete_sql = "DELETE FROM loans WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $loan_id);
            $delete_stmt->execute();

            echo "<p>Book returned successfully. Fine has been paid if applicable.</p>";
        }

        $conn->close();
    }
    ?>

</body>
</html>
