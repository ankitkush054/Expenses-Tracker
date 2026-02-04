<?php

// include 'session_check.php';
// include("config.php");
// include 'dash.php';

// if (!isset($_SESSION['user_id'])) {
//     die("User not logged in");
// }
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $user_id = $_SESSION['user_id'];
//     $a = $_POST['Amount'] ?? null;
//     $s = $_POST['Source'] ?? null;
//     $d = $_POST['Income_date'] ?? null;

//     if (!$a || !$s || !$d) {
//         die("Please fill all required fields.");
//     }

//     // Insert into database
//     $query = "INSERT INTO income (Amount, Source, Income_date, User_id) 
//               VALUES ('$a', '$s', '$d', '$user_id')";
//     $res = mysqli_query($con, $query);

//     // if ($res) {
//     //     header("location:add_income.php");
//     //     exit();
//     // } else {
//     //     echo "Failed to insert income: " . mysqli_error($con);
//     // }
// }

?>


<?php
include 'session_check.php';
include("config.php");
include 'dash.php';
include 'sendEmail.php'; // ✅ PHPMailer function include

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $a = $_POST['Amount'] ?? null;
    $s = $_POST['Source'] ?? null;
    $d = $_POST['Income_date'] ?? null;

    if (!$a || !$s || !$d) {
        die("⚠️ Please fill all required fields.");
    }

    // ✅ Insert into database safely
    $query = "INSERT INTO income (Amount, Source, Income_date, User_id) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("dssi", $a, $s, $d, $user_id);

    if ($stmt->execute()) {
        // ✅ Fetch user's email
        $userQuery = $con->prepare("SELECT email FROM users WHERE User_id = ?");
        $userQuery->bind_param("i", $user_id);
        $userQuery->execute();
        $result = $userQuery->get_result();
        $user = $result->fetch_assoc();
        $user_email = $user['email'] ?? null;
        $userQuery->close();

        // ✅ Calculate total balance (income - expense)
        $income_sum = $con->query("SELECT SUM(Amount) AS total_income FROM income WHERE User_id = $user_id")->fetch_assoc()['total_income'] ?? 0;
        $expense_sum = $con->query("SELECT SUM(Amount) AS total_expense FROM expenses WHERE User_id = $user_id")->fetch_assoc()['total_expense'] ?? 0;
        $balance = $income_sum - $expense_sum;

        // ✅ Send email if user has an email
        if (!empty($user_email)) {
            $formattedDate = date("d-m-Y", strtotime($d));
            $subject = "New Income Added ";
            $message = "
                <h3>Hello!</h3>
                <p>You just added a new <strong>income</strong> to your Expense Tracker:</p>
                <ul>
                    <li><strong>Source:</strong> {$s}</li>
                    <li><strong>Amount:</strong> ₹{$a}</li>
                    <li><strong>Date:</strong> {$formattedDate}</li>
                </ul>
                <p><strong>💵 Your Current Total Balance:</strong> ₹{$balance}</p>
                <p>Thank you for using our Expense Tracker! 💰</p>
            ";

            // ✅ Send email now
            if (sendEmail($user_email, $subject, $message)) {
                echo "<script>alert('✅ Income added & Email sent successfully!'); window.location.href='dashboard.php';</script>";
            } else {
                echo "<script>alert('✅ Income added but ❌ Email failed to send.'); window.location.href='dashboard.php';</script>";
            }
        } else {
            echo "<script>alert('✅ Income added but no user email found!'); window.location.href='dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('❌ Failed to insert income: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
?>

<style>
   /* ===== POPUP CONTAINER ===== */
#incomePopup {
    display: block; /* show popup */
    position: relative; 
    margin: 50px auto 0 auto; /* Vertical spacing */
    width: 500px; /* Desktop width */
    max-width: 95%; /* Mobile friendly */
}

/* ===== POPUP CONTENT ===== */
.popup-content {
    background: #fff;
    padding: 25px; /* thoda extra padding */
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    position: relative;
    width: auto;
}

/* Close button */
.close-button {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 24px;
    cursor: pointer;
}

/* Form elements */
.popup-content input[type="text"],
.popup-content input[type="number"],
.popup-content input[type="date"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.popup-content label {
    font-weight: 500;
    margin-top: 8px;
    display: block;
}

/* Submit button */
.popup-content button[type="submit"] {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    background-color: #4CAF50;
    color: white;
    cursor: pointer;
    font-size: 15px;
}

/* ===== RESPONSIVE ===== */

/* Tablets (up to 768px) */
@media (max-width: 768px) {
    #incomePopup {
        width: 90%;
        margin: 40px auto 0 auto;
    }

    .popup-content {
        padding: 20px;
    }

    .popup-content button[type="submit"] {
        width: 100%;
    }
}

/* Mobile (up to 480px) */
@media (max-width: 480px) {
    #incomePopup {
        width: 95%;
        margin: 20px auto 0 auto;
    }

    .popup-content {
        padding: 15px;
    }

    .close-button {
        font-size: 22px;
        top: 8px;
        right: 10px;
    }

    .popup-content input[type="text"],
    .popup-content input[type="number"],
    .popup-content input[type="date"] {
        font-size: 13px;
        padding: 8px;
    }

    .popup-content button[type="submit"] {
        font-size: 14px;
        padding: 10px 0;
    }

}
</style>

    <main>
        <div id="incomePopup">
            <form id="incomeForm" action="add_income.php" method="post">
                <div class="popup-content">
                    <span class="close-button" id="closeIncomePopup">&times;</span>
                    <h2>Add Income</h2>
                    <label for="incomeSource">Source:</label>
                    <input type="text" id="Source" name="Source" required>

                    <label for="incomeAmount">Amount:</label>
                    <input type="number" id="Amount" name="Amount" required>

                    <label for="incomeDate">Date:</label>
                    <input type="date" id="Income_date" name="Income_date">

                    <button type="submit">Add</button>
                </div>
            </form>
        </div>

    </main>

</body>

</html>