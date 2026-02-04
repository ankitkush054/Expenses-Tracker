<?php
include("config.php");
include("session_check.php");
include 'dash.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// === Expenses by "Category" (Description) ===
$categoryLabels = [];
$categoryData = [];

$sql = "SELECT description, SUM(amount) as total 
        FROM expenses 
        WHERE user_id = ? 
        GROUP BY description";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $categoryLabels[] = $row['description'];
    $categoryData[] = $row['total'];
}

// === Monthly Expenses ===
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$monthlyExpenses = [];

foreach ($months as $m) {
    $monthNumber = date('n', strtotime($m));
    $sql = "SELECT SUM(amount) as total 
            FROM expenses 
            WHERE user_id = ? AND MONTH(expense_date) = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $user_id, $monthNumber);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $monthlyExpenses[] = $res['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Expense Charts</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 20px;
    }
    section.r {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: center;
    }
    .chart-box {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      width: 500px;
      max-width: 100%;
    }
    canvas {
      width: 100%;
      height: 300px;
    }
    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<section class="r">
  <div class="chart-box">
    <h2>Expenses by Category</h2>
    <canvas id="categoryChart"></canvas>
  </div>

  <div class="chart-box">
    <h2>Monthly Expense Trend</h2>
    <canvas id="trendChart"></canvas>
  </div>
</section>

<script>
const categoryLabels = <?php echo json_encode($categoryLabels); ?>;
const categoryData = <?php echo json_encode($categoryData); ?>;
const trendLabels = <?php echo json_encode($months); ?>;
const monthlyExpenses = <?php echo json_encode($monthlyExpenses); ?>;

// === Pie Chart ===
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
  type: 'pie',
  data: {
    labels: categoryLabels,
    datasets: [{
      data: categoryData,
      backgroundColor: [
        '#8a8eff','#aaaaff','#cbcbff','#ececff','#f0f0ff',
        '#ffd6a5','#fdffb6','#caffbf','#9bf6ff','#a0c4ff'
      ],
      borderWidth: 1
    }]
  },
  options: { responsive: true }
});

// === Line Chart ===
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
  type: 'line',
  data: {
    labels: trendLabels,
    datasets: [{
      label: 'Expenses',
      data: monthlyExpenses,
      borderColor: '#4b4b4b',
      backgroundColor: 'rgba(200,200,200,0.3)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true }
    }
  }
});
</script>

</body>
</html>
