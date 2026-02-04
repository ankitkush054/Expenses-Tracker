<?php
include("config.php");
include("session_check.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Include FPDF
if (!class_exists('FPDF') && file_exists(__DIR__ . '/fpdf186/fpdf.php')) {
    require_once __DIR__ . '/fpdf186/fpdf.php';
}

// === Handle date filter ===
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to   = isset($_GET['to'])   ? $_GET['to']   : '';

$whereIncome = "WHERE User_id = ?";
$whereExpense = "WHERE User_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($from) && !empty($to)) {
    $whereIncome .= " AND Income_date BETWEEN ? AND ?";
    $whereExpense .= " AND Expense_date BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
    $types .= "ss";
}

// === Fetch data ===
$transactions = [];

// Income
$query = "SELECT Source AS description, Amount AS amount, Income_date AS date FROM income $whereIncome";
$income_stmt = $con->prepare($query);
$income_stmt->bind_param($types, ...$params);
$income_stmt->execute();
$res = $income_stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $transactions[] = [
        'type' => 'Income',
        'description' => $row['description'],
        'amount' => $row['amount'],
        'date' => $row['date']
    ];
}
$income_stmt->close();

// Expense
$query = "SELECT Description AS description, Amount AS amount, Expense_date AS date FROM expenses $whereExpense";
$expense_stmt = $con->prepare($query);
$expense_stmt->bind_param($types, ...$params);
$expense_stmt->execute();
$res = $expense_stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $transactions[] = [
        'type' => 'Expense',
        'description' => $row['description'],
        'amount' => $row['amount'],
        'date' => $row['date']
    ];
}
$expense_stmt->close();

// Sort all transactions by date ascending
usort($transactions, function($a, $b) {
    return strtotime($a['date']) <=> strtotime($b['date']);
});

// === Calculate totals ===
$total_income = 0;
$total_expense = 0;

foreach ($transactions as $t) {
    if ($t['type'] === 'Income') {
        $total_income += $t['amount'];
    } else {
        $total_expense += $t['amount'];
    }
}

$balance = $total_income - $total_expense;

// === Determine balance message and color ===
if ($balance > 0) {
    $msg = "🟢 You're saving money!";
    $color = "green";
} elseif ($balance == 0) {
    $msg = "⚪ You broke even!";
    $color = "gray";
} else {
    $msg = "🔴 You're overspending!";
    $color = "red";
}

// === PDF Download ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_pdf'])) {
    if (!class_exists('FPDF')) {
        die("FPDF library not found. Please place fpdf.php in fpdf186/ directory.");
    }

    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Income & Expense Report (Date-wise)', 0, 1, 'C');
    $pdf->Ln(5);

    if (!empty($from) && !empty($to)) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, "From: $from  To: $to", 0, 1, 'C');
        $pdf->Ln(3);
    }

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(40, 9, 'Type', 1, 0, 'C', true);
    $pdf->Cell(110, 9, 'Description', 1, 0, 'C', true);
    $pdf->Cell(40, 9, 'Amount', 1, 0, 'C', true);
    $pdf->Cell(50, 9, 'Date', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    foreach ($transactions as $t) {
        $pdf->Cell(40, 8, $t['type'], 1);
        $pdf->Cell(110, 8, substr($t['description'], 0, 60), 1);
        $pdf->Cell(40, 8, $t['amount'], 1);
        $pdf->Cell(50, 8, $t['date'], 1, 1);
    }

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Total Income: Rs.{$total_income} | Total Expense: Rs.{$total_expense} | Balance: Rs.{$balance}", 0, 1, 'C');
  if ($balance > 0) {
    $pdf->SetTextColor(0, 128, 0); // Green
    $pdf->Cell(0, 10, "You're saving money!", 0, 1, 'C');
} elseif ($balance == 0) {
    $pdf->SetTextColor(128, 128, 128); // Gray
    $pdf->Cell(0, 10, "You're breaking even!", 0, 1, 'C');
} else {
    $pdf->SetTextColor(255, 0, 0); // Red
    $pdf->Cell(0, 10, "You're overspending!", 0, 1, 'C');
}



    $filename = "Expense_Report_" . date("Ymd_His") . ".pdf";
    $pdf->Output('D', $filename);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Date-wise Report | Expensio</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .report-container { margin-left: 250px; padding: 40px; }
    form button, form input[type="date"] {
        background-color: rgba(255, 255, 255, 1);
         color: #000000ff;
        border: solid ; padding: 10px 20px; margin-right: 10px;
        border-radius: 5px; cursor: pointer;
    }
    input[type="date"] { color: black; background: #f9f9f9; border: 1px solid #ccc; }
    .clear-btn { background-color: #888 !important; }
    table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    th { background: #f4f4f4; }
    .summary { margin-top: 25px; font-size: 18px; font-weight: bold; }
    .summary p { margin: 5px 0; font-size: 10px;}
  </style>
</head>
<body>
<?php include("dash.php"); ?>

<div class="report-container">
  <h2>Income & Expense Report (Date-wise)</h2>

  <form method="get">
    <label>From: &nbsp; </label><input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
    <label>To:&nbsp; </label><input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
    <button type="submit">Filter</button>
    <a href="download_report.php" class="clear-btn" style="padding:10px 20px; text-decoration:none; color:white;">Clear Filter</a>
  </form>
<form method="post" style="margin-top:10px;">
  <button type="submit" name="download_pdf" style="background-color:#dc3545; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">
    ⬇ Download PDF
  </button>
</form>


  <table>
    <tr>
      <th>Type</th>
      <th>Description</th>
      <th>Amount</th>
      <th>Date</th>
    </tr>
    <?php foreach ($transactions as $t): ?>
    <tr>
      <td><?= htmlspecialchars($t['type']) ?></td>
      <td><?= htmlspecialchars($t['description']) ?></td>
      <td><?= htmlspecialchars($t['amount']) ?></td>
      <td><?= htmlspecialchars($t['date']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <style>
  .summary-box {
    margin-top: 25px;
    padding: 20px;
    border-radius: 10px;
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    color: white;
  }
  .green-box { background-color: #28a745; }
  .gray-box { background-color: #6c757d; }
  .red-box { background-color: #dc3545; }
  .summary-values {
    background: #f8f9fa;
    color: #333;
    padding: 15px;
     font-size: 15px;
    font-weight: bold;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-top: 20px;
  }
</style>

<div class="summary-values">
  <p>Total Income: ₹<?= $total_income ?></p>
  <p>Total Expense: ₹<?= $total_expense ?></p>
  <p>Balance: ₹<?= $balance ?></p>
</div>

<div class="summary-box 
  <?php 
    if ($balance > 0) echo 'green-box';
    elseif ($balance == 0) echo 'gray-box';
    else echo 'red-box';
  ?>">
  <?= $msg ?>
</div>

</div>
</body>
</html>
