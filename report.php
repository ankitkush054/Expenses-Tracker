<?php
// session_start();
ob_start(); // Start buffering output

include 'config.php';
include 'session_check.php';
include 'dash.php';
require('fpdf.php'); // Make sure fpdf.php is in the same folder

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id'];

// Fetch data
$sql = "SELECT Description, Amount, Expense_date FROM expenses WHERE User_id = '$user_id'";
$result = mysqli_query($con, $sql);

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Expense Report',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(60,10,'Description',1);
$pdf->Cell(40,10,'Amount',1);
$pdf->Cell(40,10,'Date',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
while ($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(60,10,$row['Description'],1);
    $pdf->Cell(40,10,$row['Amount'],1);
    $pdf->Cell(40,10,$row['Expense_date'],1);
    $pdf->Ln();
}

// Download PDF
$pdf->Output('D', 'expenses_report.pdf');
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download Report</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; margin:0; padding:0; }
        .container { max-width: 700px; margin: 80px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); text-align: center; }
        h2 { margin-bottom: 20px; color: #333; }
        form { margin-top: 20px; }
        select, button { padding: 10px 15px; margin: 10px; font-size: 16px; }
        button { cursor: pointer; background: #007BFF; color: white; border: none; border-radius: 5px; transition: 0.3s; }
        button:hover { background: #0056b3; }
        label { margin-right: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Download Your Report (PDF)</h2>
        <form method="post" action="">
            <div>
                <label for="type">Select Report:</label>
                <select name="type" id="type" required>
                    <option value="expenses">Expenses</option>
                    <option value="income">Income</option>
                </select>
            </div>
            <div>
                <label for="time_filter">Select Time:</label>
                <select name="time_filter" id="time_filter" required>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            <div>
                <button type="submit" name="download">Download PDF</button>
            </div>
        </form>
    </div>
</body>
</html>
