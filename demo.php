<?php
// ========== STEP 1: SESSION & SECURITY (MUST be at the very top) ==========
session_start();

// Agar user logged-in nahi hai, to seedha login page par bhejo
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Browser ko page cache karne se roko (Back button problem ka final fix)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


// ========== STEP 2: DATABASE CONNECTION & CALCULATIONS ==========
include("config.php");

// User ID aur Username ko variable mein save kar lo
$user_id = $_SESSION['user_id'];
$loggedInUsername = htmlspecialchars($_SESSION['Username']);

// --- Totals Calculation ---
$totalIncome = 0;
$totalExpense = 0;

// Prepared Statement for Income (Secure way)
$stmt = $con->prepare("SELECT SUM(Amount) AS total_income FROM income WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultIncome = $stmt->get_result();
$rowIncome = $resultIncome->fetch_assoc();
$totalIncome = $rowIncome['total_income'] ?? 0;
$stmt->close();

// Prepared Statement for Expense (Secure way)
$stmt = $con->prepare("SELECT SUM(Amount) AS total_expense FROM expenses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultExpense = $stmt->get_result();
$rowExpense = $resultExpense->fetch_assoc();
$totalExpense = $rowExpense['total_expense'] ?? 0;
$stmt->close();

$totalBalance = $totalIncome - $totalExpense;

// --- Fetch Recent Transactions ---
$transactions = []; // Khaali array banao transactions ke liye
$sql = "
  SELECT Income_date AS t_date, Source AS description, 'Income' AS category, Amount AS amount, income_id AS id FROM income WHERE user_id = ?
  UNION ALL
  SELECT Expense_date AS t_date, Description AS description, 'Expense' AS category, Amount AS amount, expense_id AS id FROM expenses WHERE user_id = ?
  ORDER BY t_date DESC
";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row; // Har transaction ko array mein daalo
    }
}
$stmt->close();


// ========== STEP 3: DATABASE CONNECTION BAND KAR DO ==========
$con->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expensio | Dashboard </title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="dchart.css">
  <link rel="stylesheet" href="dcard.css">
  <link rel="stylesheet" href="dbutton.css">
  <link rel="stylesheet" href="dtransaction.css">
</head>

<body>
<header>
  <nav style="display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; background: #f5f5f5;">
    <div></div>
    <div style="display: flex; align-items: center;">
      <span style="font-weight:bold; color:#434343;">👤 <?php echo $loggedInUsername; ?></span>
    </div>
  </nav>
</header>

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="img/expense-removebg-preview.png" alt="logo" />
        <h2>Expensio</h2>
      </div>

      <ul class="sidebar-links">
        <li><a href="dashboard.php"><span class="material-symbols-outlined">dashboard</span>Dashboard</a></li>
        <li><a href="#"><span class="material-symbols-outlined">add_card</span>Add Expense</a></li>
        <li><a href="logout.php"><span class="material-symbols-outlined">logout</span>Logout</a></li>
            </ul>
</aside>

<main>
    <section id="dashboard" class="c-container">
        <div class="card">
            <i class="fas fa-wallet"></i>
            <h3>Total Balance</h3>
            <p class="h4 card-value balance <?php echo ($totalBalance >= 0) ? 'positive' : 'negative'; ?>">₹ <?php echo number_format($totalBalance, 2); ?></p>
        </div>
        <div class="card">
            <i class="fas fa-arrow-up"></i>
            <h3>Total Income</h3>
            <p class="h4 card-value income">₹ <?php echo number_format($totalIncome, 2); ?></p>
        </div>
        <div class="card">
            <i class="fas fa-arrow-down"></i>
            <h3>Total Expenses</h3>
            <p class="h4 card-value expense">₹ <?php echo number_format($totalExpense, 2); ?></p>
        </div>
    </section>

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
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $row): ?>
                            <?php
                                $amountClass = ($row['category'] === 'Income') ? 'income' : 'expense';
                                $sign = ($row['category'] === 'Income') ? '+ ' : '- ';
                            ?>
                            <tr>
                                <td data-label='Date'><?php echo htmlspecialchars($row['t_date']); ?></td>
                                <td data-label='Description'><?php echo htmlspecialchars($row['description']); ?></td>
                                <td data-label='Amount' class='amount <?php echo $amountClass; ?>'><?php echo $sign; ?>₹<?php echo number_format($row['amount'], 2); ?></td>
                                <td class='actions'>
                                    <button class='edit-btn' title='Edit'><span class='material-symbols-outlined'>edit</span></button>
                                    <button class='delete-btn' title='Delete'><span class='material-symbols-outlined'>delete</span></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan='4'>No recent transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    
    </main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<script src="dbutton.js"></script>
</body>
</html>