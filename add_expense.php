 <?php
// include 'session_check.php';
// include("config.php");
// include 'dash.php';

// if (!isset($_SESSION['user_id'])) {
//     die("User not logged in");
// }

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // Check if required fields exist
//     if(isset($_POST['Description'], $_POST['Amount'], $_POST['Expense_date'])) {
//         $description = $_POST['Description'];
//         $amount = $_POST['Amount'];
//         $date = $_POST['Expense_date'];
//         $user_id = $_SESSION['user_id'];
//         $sql = "INSERT INTO expenses (Description, Amount, Expense_date, User_id) VALUES (?, ?, ?, ?)";
        
//         // Prepare the statement
//         if ($stmt = $con->prepare($sql)) {
            
//             // Bind parameters: 's' for string, 'd' for double/decimal, 'i' for integer
//             $stmt->bind_param("sdsi", $description, $amount, $date, $user_id);

//             // Execute the statement
//             if ($stmt->execute()) {
//                 // Redirect to dashboard after adding
//                 header("Location: dashboard.php");
//                 exit();
//             } else {
//                 echo "Error: " . $stmt->error;
//             }
            
//             // Close statement
//             $stmt->close();

//         } else {
//             echo "Error preparing statement: " . $con->error;
//         }

//     } else {
//         echo "All fields are required!";
//     }
// }
?>
<!--
<style>
/* ===== GENERAL BODY STYLING ===== */
body {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    background: #f4f4f4;
    margin: 0;
    font-family: Arial, sans-serif;
}

/* ===== POPUP CONTAINER ===== */
#expensePopup {
    display: block; /* show popup */
    position: relative; 
    margin: 50px auto 0 auto; /* This centers the form */
    width: 100%; 
    max-width: 95%; /* Mobile friendly */
}

/* ===== POPUP CONTENT ===== */
.popup-content {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    position: relative;
    width: auto;
}

/* ===== CLOSE BUTTON ===== */
.close-button {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}

/* ===== FORM ELEMENTS ===== */
.popup-content label {
    display: block;
    margin-top: 10px;
    font-weight: 500;
}

.popup-content input[type="text"],
.popup-content input[type="number"],
.popup-content input[type="date"] {
    width: 100%;
    padding: 8px 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 14px;
}

/* ===== SUBMIT BUTTON ===== */
.popup-content button[type="submit"] {
    margin-top: 15px;
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.popup-content button[type="submit"]:hover {
    background-color: #45a049;
}

/* ===== RESPONSIVE STYLING ===== */

/* Tablets (up to 768px) */
@media (max-width: 768px) {
    #expensePopup {
        margin: 40px auto 0 auto;
    }

    .popup-content {
        padding: 20px;
    }
}

/* Mobile (up to 480px) */
@media (max-width: 480px) {
    #expensePopup {
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
        font-size: 14px;
        padding: 8px 10px;
    }

    .popup-content button[type="submit"] {
        font-size: 15px;
        padding: 10px 0;
    }
}
</style>

<main>
    <div id="expensePopup">
        <form id="expenseForm" action="add_expense.php" method="post">
            <div class="popup-content">
                <span class="close-button" id="closeExpensePopup">&times;</span>
                <h2>Add Expense</h2>
                <label for="expenseDescription">Description:</label>
                <input type="text" id="Description" name="Description" required>

                <label for="expenseAmount">Amount:</label>
                <input type="number" id="Amount" name="Amount" required>

                <label for="expenseDate">Date:</label>
                <input type="date" id="Expense_date" name="Expense_date" required>

                <button type="submit">Add</button>
            </div>
        </form>
    </div>
</main> -->

<?php
include 'session_check.php';
include("config.php");
include 'dash.php';
include 'sendEmail.php'; // ✅ For sending email

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['Description'] ?? '';
    $amount = $_POST['Amount'] ?? 0;
    $date = $_POST['Expense_date'] ?? '';
    $forceAdd = $_POST['forceAdd'] ?? 0;
    $user_id = $_SESSION['user_id'];

    if (empty($description) || empty($amount) || empty($date)) {
        echo "<script>alert('⚠️ All fields are required!'); window.location='dashboard.php';</script>";
        exit();
    }

    // ✅ 1️⃣ Current Balance
    $income_result = $con->query("SELECT SUM(Amount) AS total_income FROM income WHERE User_id = $user_id");
    $expense_result = $con->query("SELECT SUM(Amount) AS total_expense FROM expenses WHERE User_id = $user_id");

    $income = $income_result->fetch_assoc()['total_income'] ?? 0;
    $expense = $expense_result->fetch_assoc()['total_expense'] ?? 0;
    $balance = $income - $expense;

    // ✅ 2️⃣ Balance check
    if ($amount > $balance && !$forceAdd) {
        // ⚠️ Show popup using JS, but logic is fully PHP-driven
        echo "
        <script>
        const userConfirmed = confirm('⚠️ This expense (₹$amount) exceeds your current balance (₹$balance). Do you still want to continue?');
        if (userConfirmed) {
            // Re-submit with forceAdd = 1
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'add_expense.php';

            const fields = {
                Description: '".addslashes($description)."',
                Amount: '".addslashes($amount)."',
                Expense_date: '".addslashes($date)."',
                forceAdd: '1'
            };

            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        } else {
            alert('❌ Expense cancelled.');
            window.location.href = 'dashboard.php';
        }
        </script>
        ";
        exit();
    }

    // ✅ 3️⃣ Insert Expense
    $stmt = $con->prepare("INSERT INTO expenses (Description, Amount, Expense_date, User_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $description, $amount, $date, $user_id);

    if ($stmt->execute()) {
        // ✅ Fetch user email
        $user_query = $con->prepare("SELECT email FROM users WHERE User_id = ?");
        $user_query->bind_param("i", $user_id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        $user_email = $user_result->fetch_assoc()['email'] ?? null;
        $user_query->close();

        // ✅ Send Email
        if ($user_email) {
            $subject = "New Expense Added 💸";
            $formatted_date = date("d-m-Y", strtotime($date));
            $message = "
                <h3>Hello!</h3>
                <p>You just added a new expense:</p>
                <ul>
                    <li><strong>Description:</strong> {$description}</li>
                    <li><strong>Amount:</strong> ₹{$amount}</li>
                    <li><strong>Date:</strong> {$formatted_date}</li>
                </ul>
                <p>Thank you for using Expense Tracker 💰</p>
            ";
            sendEmail($user_email, $subject, $message);
        }

        echo "<script>alert('✅ Expense added successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('❌ Error adding expense: " . addslashes($stmt->error) . "');</script>";
    }

    $stmt->close();
}
?>




<!-- ================== HTML + CSS ================== -->
<style>
body {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    background: #f4f4f4;
    margin: 0;
    font-family: Arial, sans-serif;
}

#expensePopup {
    display: block;
    position: relative;
    margin: 50px auto 0 auto;
    width: 100%;
    max-width: 95%;
}

.popup-content {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    position: relative;
    width: auto;
}

.close-button {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}

.popup-content label {
    display: block;
    margin-top: 10px;
    font-weight: 500;
}

.popup-content input[type="text"],
.popup-content input[type="number"],
.popup-content input[type="date"] {
    width: 100%;
    padding: 8px 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 14px;
}

.popup-content button[type="submit"] {
    margin-top: 15px;
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.popup-content button[type="submit"]:hover {
    background-color: #45a049;
}
</style>

<main>
    <div id="expensePopup">
        <form id="expenseForm" action="add_expense.php" method="post">
            <div class="popup-content">
                <span class="close-button" id="closeExpensePopup">&times;</span>
                <h2>Add Expense</h2>

                <label for="Description">Description:</label>
                <input type="text" id="Description" name="Description" required>

                <label for="Amount">Amount:</label>
                <input type="number" id="Amount" name="Amount" required>

                <label for="Expense_date">Date:</label>
                <input type="date" id="Expense_date" name="Expense_date" required>

                <button type="submit">Add</button>
            </div>
        </form>
    </div>
</main>
