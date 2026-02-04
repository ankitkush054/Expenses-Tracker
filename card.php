<?php
include 'session_check.php';

include("config.php");

// --- Fetch Recent Transactions (adjust query as needed) ---
$sqlTransactions = "SELECT
                        CASE
                            WHEN i.Income_id IS NOT NULL THEN 'Income'
                            WHEN e.Expense_id IS NOT NULL THEN 'Expense'
                        END AS transaction_type,
                        COALESCE(i.Income_date, e.Expense_date) AS transaction_date,
                        COALESCE(i.Source, e.Description) AS description,
                        COALESCE(i.Amount, e.Amount) AS amount
                    FROM income i
                    LEFT JOIN expenses e ON 1=1
                    WHERE i.User_id = 1 -- Replace with the actual User ID from session/auth
                       OR e.User_id = 1 -- Replace with the actual User ID from session/auth
                    ORDER BY transaction_date DESC
                    LIMIT 5; -- Limit to the last 5 transactions";

$resultTransactions = $con->query($sqlTransactions);
$transactions = [];
if ($resultTransactions && $resultTransactions->num_rows > 0) {
    while ($row = $resultTransactions->fetch_assoc()) {
        $transactions[] = $row;
    }
} else {
    echo "Error fetching transactions: " . $con->error . "<br>";
}

// Close the database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px; /* Adjust width as needed */
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .income {
            color: green;
        }

        .expense {
            color: red;
        }

        .balance.positive {
            color: blue;
        }

        .balance.negative {
            color: orange;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
        }

        .transactions-table th, .transactions-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .transactions-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .transactions-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="authentication-wrapper">
        <div class="authentication-inner">
            <div class="card">
                <div class="card-body">
                    <div class="app-brand justify-content-center">
                        <a href="#" class="app-brand-link gap-2">
                            <span class="app-brand-text demo text-body fw-bolder">Dashboard</span>
                        </a>
                    </div>


                        <div>
                            <h3>Recent Transactions</h3>
                            <?php if (!empty($transactions)): ?>
                                <table class="transactions-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                                <td class="<?php echo ($transaction['transaction_type'] === 'Income') ? 'income' : 'expense'; ?>">
                                                    ₹ <?php echo number_format($transaction['amount'], 2); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No recent transactions found.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <a href="#">View All Transactions</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>