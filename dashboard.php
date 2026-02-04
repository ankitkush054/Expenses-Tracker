<?php

include 'session_check.php';
include("config.php");
include 'dash.php';
?>







  <main>

<?php
if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id'];

// --- Fetch Total Income for the logged-in user ---
$sqlIncome = "SELECT SUM(Amount) AS totalIncome FROM income WHERE User_id = '$user_id'";
$resultIncome = $con->query($sqlIncome);
$rowIncome = $resultIncome->fetch_assoc();
$totalIncome = $rowIncome['totalIncome'] ?? 0;  // Use 0 if NULL

// --- Fetch Total Expenses for the logged-in user ---
$sqlExpense = "SELECT SUM(Amount) AS totalExpense FROM expenses WHERE User_id = '$user_id'";
$resultExpense = $con->query($sqlExpense);
$rowExpense = $resultExpense->fetch_assoc();
$totalExpense = $rowExpense['totalExpense'] ?? 0;

// --- Calculate Total Balance ---
$totalBalance = $totalIncome - $totalExpense;
?>
<?php
// include 'session_check.php';
// include("config.php");
// include 'dash.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id'];

// Messages
$income_msg = "";
$expense_msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(isset($_POST['form_type'])) {
        $type = $_POST['form_type'];

       if($type === "income") {
    $amount = $_POST['Amount'] ?? null;
    $source = $_POST['Source'] ?? null;
    $date = $_POST['Income_date'] ?? null;

    if(!$amount || !$source || !$date){
        $income_msg = "⚠️ All income fields are required!";
    } else {
        $query = "INSERT INTO income (Amount, Source, Income_date, User_id) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($query);

        if($stmt){
            $stmt->bind_param("dssi", $amount, $source, $date, $user_id);
            if($stmt->execute()){
                $income_msg = "✅ Income added successfully!";

                // ✅ Recalculate total balance after income insert
                $sqlIncome = "SELECT SUM(Amount) AS totalIncome FROM income WHERE User_id = '$user_id'";
                $sqlExpense = "SELECT SUM(Amount) AS totalExpense FROM expenses WHERE User_id = '$user_id'";
                $resInc = $con->query($sqlIncome)->fetch_assoc();
                $resExp = $con->query($sqlExpense)->fetch_assoc();
                $totalBalance = ($resInc['totalIncome'] ?? 0) - ($resExp['totalExpense'] ?? 0);

                // ✅ Fetch user email
                $emailQuery = $con->prepare("SELECT email FROM users WHERE User_id = ?");
                $emailQuery->bind_param("i", $user_id);
                $emailQuery->execute();
                $result = $emailQuery->get_result();
                $user = $result->fetch_assoc();
                $user_email = $user['email'] ?? null;
                $emailQuery->close();

                // ✅ Send email if available
                if(!empty($user_email)) {
                    include_once 'sendEmail.php';
                    $formattedDate = date("d-m-Y", strtotime($date));
                    $subject = "💰 New Income Added!";
                    $message = "
                        <h3>Hello!</h3>
                        <p>You've successfully added new income:</p>
                        <ul>
                            <li><strong>Source:</strong> {$source}</li>
                            <li><strong>Amount:</strong> ₹{$amount}</li>
                            <li><strong>Date:</strong> {$formattedDate}</li>
                        </ul>
                        <p><strong>💼 Current Balance:</strong> ₹{$totalBalance}</p>
                        <br>
                        <p>Keep tracking your finances smartly with Expense Tracker 💚</p>
                    ";

                    // sendEmail(to, subject, message)
                    sendEmail($user_email, $subject, $message);
                }

                echo "<script>alert('✅ Income added successfully!'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
            } else {
                $income_msg = '❌ Error adding income: '.$stmt->error;
            }
            $stmt->close();
        } else {
            $income_msg = '❌ Database Error: '.$con->error;
        }
    }
}

         elseif($type === "expense") {
    $amount = $_POST['Amount'] ?? null;
    $desc = $_POST['Description'] ?? null;
    $date = $_POST['Expense_date'] ?? null;
    $forceAdd = $_POST['forceAdd'] ?? 0;

    if(!$amount || !$desc || !$date){
        $expense_msg = "⚠️ All expense fields are required!";
    } else {

        // ✅ Calculate user balance first
        $sqlIncome = "SELECT SUM(Amount) AS totalIncome FROM income WHERE User_id = '$user_id'";
        $sqlExpense = "SELECT SUM(Amount) AS totalExpense FROM expenses WHERE User_id = '$user_id'";
        $resInc = $con->query($sqlIncome)->fetch_assoc();
        $resExp = $con->query($sqlExpense)->fetch_assoc();
        $balance = ($resInc['totalIncome'] ?? 0) - ($resExp['totalExpense'] ?? 0);

        // ✅ If expense > balance and not forced, show confirm popup
        if ($amount > $balance && !$forceAdd) {
            echo "
            <script>
            if (confirm('⚠️ This expense (₹$amount) exceeds your current balance (₹$balance). Do you still want to continue?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const fields = {
                    Amount: '".addslashes($amount)."',
                    Description: '".addslashes($desc)."',
                    Expense_date: '".addslashes($date)."',
                    form_type: 'expense',
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

        // ✅ Insert Expense
        $query = "INSERT INTO expenses (Amount, Description, Expense_date, User_id) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($query);

        if($stmt){
            $stmt->bind_param("dssi", $amount, $desc, $date, $user_id);
            if($stmt->execute()){
                $expense_msg = "✅ Expense added successfully!";

                // ✅ Fetch user email
                $emailQuery = $con->prepare("SELECT email FROM users WHERE User_id = ?");
                $emailQuery->bind_param("i", $user_id);
                $emailQuery->execute();
                $result = $emailQuery->get_result();
                $user = $result->fetch_assoc();
                $user_email = $user['email'] ?? null;
                $emailQuery->close();

                // ✅ Send email if available
                if(!empty($user_email)) {
                    include_once 'sendEmail.php';
                    $formattedDate = date("d-m-Y", strtotime($date));
                    $subject = "New Expense Added 💸";
                    $message = "
                        <h3>Hello!</h3>
                        <p>You just added a new expense:</p>
                        <ul>
                            <li><strong>Description:</strong> {$desc}</li>
                            <li><strong>Amount:</strong> ₹{$amount}</li>
                            <li><strong>Date:</strong> {$formattedDate}</li>
                        </ul>
                        <p>Thank you for using Expense Tracker 💰</p>
                    ";
                    sendEmail($user_email, $subject, $message);
                }

                echo "<script>alert('✅ Expense added successfully!'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
            } else {
                $expense_msg = '❌ Error adding expense: '.$stmt->error;
            }
            $stmt->close();
        } else {
            $expense_msg = '❌ Database Error: '.$con->error;
        }
    }
}



    }
}
?>


<section id="dashboard" class="c-container">
    <div class="card">
        <i class="fas fa-wallet" style="color: #434343;"></i>
        <h3>Total Balance</h3>
        <p class="h4 card-value balance <?php echo ($totalBalance >= 0) ? 'positive' : 'negative'; ?>">
            ₹ <?php echo number_format($totalBalance, 2); ?>
        </p>
    </div>

    <div class="card">
        <i class="fas fa-arrow-up" style="color: #434343;"></i>
        <h3>Total Income</h3>
        <p class="h4 card-value income">
            ₹ <?php echo number_format($totalIncome, 2); ?>
        </p>
    </div>

    <div class="card">
        <i class="fas fa-arrow-down" style="color: #434343;"></i>
        <h3>Total Expenses</h3>
        <p class="h4 card-value expense">
            ₹ <?php echo number_format($totalExpense, 2); ?>
        </p>
    </div>
</section>




    <section id="Expense" id="Income">
      <div class="button-container" id="Income">
        <button class="add-button" id="addIncomeBtn">Add Income<span class="material-symbols-outlined">
            arrow_upward
          </span></button>

        <button class="expense-button" id="addExpenseBtn">Add Expense<span class="material-symbols-outlined">
            arrow_downward
          </span></button>
      </div>

<!-- INCOME POPUP -->
<div id="incomePopup" class="popup">
  <form method="post" action="">
    <div class="popup-content">
      <span class="close-button" onclick="document.getElementById('incomePopup').style.display='none';">&times;</span>
      <h2>Add Income</h2>
      <?php if($income_msg) echo "<p style='color:red;'>$income_msg</p>"; ?>

      <label for="incomeSource">Source:</label>
      <input type="text" id="Source" name="Source" required>

      <label for="incomeAmount">Amount:</label>
      <input type="number" id="Amount" name="Amount" required>

      <label for="incomeDate">Date:</label>
      <input type="date" id="Income_date" name="Income_date">

      <input type="hidden" name="form_type" value="income">
      <button type="submit">Add</button>
    </div>
  </form>
</div>

<!-- EXPENSE POPUP -->
<div id="expensePopup" class="popup">
  <form method="post" action="">
    <div class="popup-content">
      <span class="close-button" onclick="document.getElementById('expensePopup').style.display='none';">&times;</span>
      <h2>Add Expense</h2>

      <label for="expenseDescription">Description:</label>
      <input type="text" id="Description" name="Description" required>

      <label for="expenseAmount">Amount:</label>
      <input type="number" id="Amount" name="Amount" required>

      <label for="expenseDate">Date:</label>
      <input type="date" id="Expense_date" name="Expense_date">

      <input type="hidden" name="form_type" value="expense">
      <button type="submit">Add</button>
    </div>
  </form>
</div>
<style>
.popup {
    display:block;
    position: relative;
    margin: 50px auto;
    width: 500px;
    max-width: 95%;
}
.popup-content {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    width: auto;
    position: relative;
}
.close-button {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 24px;
    cursor: pointer;
}
.popup-content input, .popup-content button {
    width: 100%;
    padding: 10px;
    margin: 8px 0 12px;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box;
}
.popup-content button {
    background: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
}
@media(max-width:768px){
    .popup { width:90%; margin:40px auto; }
    .popup-content { padding:20px; }
}
@media(max-width:480px){
    .popup { width:95%; margin:20px auto; }
    .popup-content { padding:15px; }
    .close-button { font-size:22px; top:8px; right:10px; }
}

</style>


<?php
// session_start();
// include("config.php");

$user_id = $_SESSION['user_id']; // Make sure user is logged in

// Fetch income + expense transactions
$sql = "
  SELECT Income_id AS t_id, Income_date AS t_date, Source AS description, 'Income' AS category, Amount AS amount 
  FROM income
  WHERE user_id = '$user_id'
  UNION ALL
  SELECT Expense_id AS t_id, Expense_date AS t_date, Description AS description, 'Expense' AS category, Amount AS amount 
  FROM expenses
  WHERE user_id = '$user_id'
  ORDER BY t_date DESC
";

$result = $con->query($sql);
// if (!$result) {
//     echo "SQL Error: " . $con->error;
// } else {
//     echo "Rows found: " . $result->num_rows;
// }

?>

<style>.filter-container {
    margin-bottom: 20px;
     display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
}

.filter-container label {
    margin-right: 10px;
    font-weight: 600;

}

.filter-container select {
    padding: 10px 15px;  /* Bigger padding */
    border-radius: 8px;  /* More rounded corners */
    border: 1px solid #ccc;
    font-size: 18px;     /* Bigger font inside dropdown */
    min-width: 180px;    /* Wider dropdown */
}
</style>

<div class="filter-container">
    <label for="transactionFilter">Filter by Type:</label>
    <select id="transactionFilter">
        <option value="all">All</option>
        <option value="Income">Income</option>
        <option value="Expense">Expense</option>
    </select>
</div>
<script>// Transaction Filter
document.getElementById('transactionFilter').addEventListener('change', function() {
    const filterValue = this.value; // all / Income / Expense
    const rows = document.querySelectorAll('.transactions-table tbody tr');

    rows.forEach(row => {
        const type = row.querySelector('.amount').classList.contains('income') ? 'Income' : 'Expense';
        if (filterValue === 'all' || filterValue === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<section id="Transaction" class="recent-transactions">
  <div class="t">
    <h2>Recent Transactions</h2>
    <table class="transactions-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $amountClass = ($row['category'] === 'Income') ? 'income' : 'expense';
              $sign = ($row['category'] === 'Income') ? '+ ' : '- ';
              echo "<tr>
                      <td data-label='Date'>" . htmlspecialchars($row['t_date']) . "</td>
                      <td data-label='Description'>" . htmlspecialchars($row['description']) . "</td>
                      <td data-label='Amount' class='amount $amountClass'>" . $sign . "₹" . number_format($row['amount'], 2) . "</td>
                      <td class='actions'>";
                       echo "<button class='edit-btn'
        data-id='" . $row['t_id'] . "'
        data-type='" . $row['category'] . "'
        data-date='" . $row['t_date'] . "'
        data-desc='" . htmlspecialchars($row['description'], ENT_QUOTES) . "'
        data-amount='" . $row['amount'] . "'
        title='Edit'>
        <span class='material-symbols-outlined'>edit</span>
      </button>";

                   echo "<button class='delete-btn' 
            data-id='" . $row['t_id'] . "' 
            data-type='" . $row['category'] . "' 
            title='Delete'>
        <span class='material-symbols-outlined'>delete</span>
      </button>";



echo "
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='4'>No recent transactions found.</td></tr>";
      }
      ?>
      </tbody>
    </table>
  </div>
</section>
<!-- Transactions table code here -->

<!-- Include Edit Transaction Popup at the end -->
 <style> 
 /* ===== POPUPS ===== */
.popup, #transactionPopup {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 1000;
}

.popup-content {
  background: #fff;
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  border-radius: 6px;
  position: relative;
}

/* Close button */
.close-button {
  position: absolute;
  right: 15px;
  top: 10px;
  font-size: 24px;
  cursor: pointer;
}

/* Buttons inside popups */
.button-group {
  display: flex;           
  gap: 10px;               
  justify-content: flex-start; 
  margin-top: 15px;
}

.button-group button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

#updateBtn {
  background-color: #4CAF50;
  color: white;
}

#cancelBtn {
  background-color: #f44336;
  color: white;
}

/* ===== POPUP FORM ELEMENTS ===== */
.popup-content input[type="text"],
.popup-content input[type="number"],
.popup-content input[type="date"] {
  width: 100%;
  padding: 8px 10px;
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

/* ===== RESPONSIVE ===== */

/* Tablets (up to 768px) */
@media (max-width: 768px) {
  .popup-content {
    max-width: 90%;
    margin: 30px auto;
    padding: 15px;
  }

  .button-group {
    flex-direction: column;
  }

  .button-group button {
    width: 100%;
    margin-bottom: 10px;
  }
}

/* Mobile (up to 480px) */
@media (max-width: 480px) {
  .popup-content {
    max-width: 95%;
    margin: 20px auto;
    padding: 12px;
  }

  .close-button {
    font-size: 20px;
    right: 10px;
    top: 5px;
  }

  .button-group {
    flex-direction: column;
  }

  .button-group button {
    width: 100%;
    margin-bottom: 8px;
    padding: 10px 0;
    font-size: 13px;
  }

  .popup-content input[type="text"],
  .popup-content input[type="number"],
  .popup-content input[type="date"] {
    font-size: 13px;
    padding: 6px 8px;
  }
}

/* ===== TRANSACTIONS TABLE ===== */
.recent-transactions {
  width: 100%;
  margin: 20px 0;
  overflow-x: auto; /* Horizontal scroll on small screens */
}

.transactions-table {
  width: 100%;
  border-collapse: collapse;
  font-family: Arial, sans-serif;
}

.transactions-table th,
.transactions-table td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

.transactions-table th {
  background-color: #f5f5f5;
  font-weight: 600;
}

.transactions-table tr:hover {
  background-color: #f1f1f1;
}

.transactions-table .income {
  color: green;
  font-weight: bold;
}

.transactions-table .expense {
  color: red;
  font-weight: bold;
}

.transactions-table .actions button {
  margin-right: 5px;
  padding: 5px 8px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.transactions-table .actions button:hover {
  opacity: 0.8;
}

/* ===== RESPONSIVE TABLE ===== */

/* Tablets (max 768px) */
@media (max-width: 768px) {
  .transactions-table th, 
  .transactions-table td {
    padding: 10px;
    font-size: 14px;
  }
}

/* Mobile (max 480px) */
@media (max-width: 480px) {
  .transactions-table {
    display: block;
    width: 100%;
  }

  .transactions-table thead {
    display: none; /* Hide table header */
  }

  .transactions-table tbody, 
  .transactions-table tr, 
  .transactions-table td {
    display: block;
    width: 100%;
  }

  .transactions-table tr {
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px;
  }

  .transactions-table td {
    text-align: right;
    padding-left: 50%;
    position: relative;
  }

  .transactions-table td::before {
    content: attr(data-label);
    position: absolute;
    left: 15px;
    width: calc(50% - 30px);
    text-align: left;
    font-weight: 600;
  }

  .transactions-table .actions button {
    padding: 6px 10px;
    font-size: 13px;
    margin-right: 5px;
  }
}


</style>
<div id="transactionPopup">
    <form id="transactionForm">
        <div class="popup-content">
            <span class="close-button" id="closePopup">&times;</span>
            <h2>Edit Transaction</h2>

            <input type="hidden" id="tId" name="tId">
            <input type="hidden" id="tType" name="tType">

            <label for="desc">Description/Source:</label>
            <input type="text" id="desc" name="desc" required>

            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" required>

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
<div class="button-group">
    <button type="submit" id="updateBtn">Update</button>
    <button type="button" id="cancelBtn">Cancel</button>
</div>

        </div>
    </form>
</div>



<!-- chart -->

</main>

<script src="dbutton.js"></script>

<!-- ✅ AI Assistant Popup -->
<style>
#chat-icon {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: #4CAF50;
  border-radius: 50%;
  width: 60px; height: 60px;
  color: white;
  font-size: 28px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  z-index: 2000;
}
#chat-popup {
  display: none;
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 300px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  padding: 10px;
  flex-direction: column;
  z-index: 2001;
}
#chat-box {
  height: 220px;
  overflow-y: auto;
  padding: 5px;
  border: 1px solid #ddd;
  margin-bottom: 8px;
}
.option-btn {
  background: #eee;
  border: none;
  margin: 4px;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
}
.option-btn:hover { background: #4CAF50; color: white; }
</style>

<div id="chat-icon">💬</div>
<div id="chat-popup">
  <div id="chat-box"></div>
</div>

<script>
const icon = document.getElementById("chat-icon");
const popup = document.getElementById("chat-popup");
const chatBox = document.getElementById("chat-box");

icon.addEventListener("click", () => {
  popup.style.display = popup.style.display === "flex" ? "none" : "flex";
  if (popup.style.display === "flex") loadStep("main");
});

function appendMessage(msg) {
  chatBox.innerHTML = msg + "<br><br>";
  chatBox.scrollTop = chatBox.scrollHeight;
}

function loadStep(step, choice="") {
  fetch("ai_fixed_chat.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: "step=" + step + "&choice=" + encodeURIComponent(choice)
  })
  .then(res => res.json())
  .then(data => {
    appendMessage(data.reply);
    if (data.options) {
      data.options.forEach(opt => {
        const btn = document.createElement("button");
        btn.className = "option-btn";
        btn.textContent = opt;
        btn.onclick = () => loadStep(data.next, opt);
        chatBox.appendChild(btn);
      });
    }
  })
  .catch(() => appendMessage("⚠️ Error connecting to AI Assistant."));
}
</script>


</body>


<script>
// deleted

document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".delete-btn").forEach(btn => {
    btn.addEventListener("click", function() {
      let tId = this.dataset.id;
let type = this.dataset.type; // Ensure exact value
      console.log("ID:", tId, "Type:", type); // Debug

      if (confirm("Do you want to delete this transaction?")) {
        fetch("delete_expenses.php", {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: "id=" + tId + "&type=" + type
        })
        .then(res => res.text())
        .then(data => {
          console.log("Response:", data); // Debug
          if (data === "success") {
            alert("Transaction deleted successfully ✅");
            location.reload();
          } else {
            alert("Error deleting transaction ❌");
          }
        });
      }
    });
  });
});


// edited
document.addEventListener("DOMContentLoaded", function() {
    const popup = document.getElementById("transactionPopup");
    const closeBtn = document.getElementById("closePopup");
    const cancelBtn = document.getElementById("cancelBtn");

    // Open popup and prefill on Edit button click
    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            popup.style.display = "block"; // Show popup

            // Prefill form values from button's data attributes
            document.getElementById("tId").value = this.dataset.id;
            document.getElementById("tType").value = this.dataset.type;
            document.getElementById("desc").value = this.dataset.desc;
            document.getElementById("amount").value = this.dataset.amount;
            document.getElementById("date").value = this.dataset.date;
        });
    });

    // Close popup
    closeBtn.addEventListener("click", () => popup.style.display = "none");
    cancelBtn.addEventListener("click", () => popup.style.display = "none");

    // Submit form via AJAX
    document.getElementById("transactionForm").addEventListener("submit", function(e) {
        e.preventDefault();

        const tId = document.getElementById("tId").value;
        const tType = document.getElementById("tType").value;
        const desc = document.getElementById("desc").value;
        const amount = document.getElementById("amount").value;
        const date = document.getElementById("date").value;

        fetch("edit_transaction.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: `id=${tId}&type=${tType}&desc=${encodeURIComponent(desc)}&amount=${amount}&date=${date}`
        })
        .then(res => res.text())
        .then(data => {
            if(data === "success") {
                alert("Transaction updated successfully ✅");
                location.reload();
            } else {
                alert("Error updating transaction ❌: " + data);
            }
        });
    });
});


</script>
<script>
window.addEventListener('online', function() {
  console.log("🌐 Internet connected! Sending pending emails...");
  fetch("process_queue.php")
    .then(response => response.text())
    .then(data => console.log("✅ Email queue processed:", data))
    .catch(err => console.error("❌ Error processing queue:", err));
});
</script>

</html>





<?php
// Close the database connection 
$con->close();
?>




