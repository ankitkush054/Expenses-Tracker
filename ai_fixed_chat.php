<?php
include("config.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["reply" => "⚠️ Please login first."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$step = $_POST['step'] ?? 'main';
$choice = $_POST['choice'] ?? '';

function getSum($con, $table, $user_id) {
    $sql = "SELECT SUM(Amount) AS total FROM $table WHERE User_id = '$user_id'";
    $result = $con->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

if ($step === "main") {
    echo json_encode([
        "reply" => "👋 Hi! What would you like to check?",
        "options" => ["Income", "Expense", "Balance"],
        "next" => "sub"
    ]);
    exit;
}

if ($step === "sub") {
    if ($choice === "Income") {
        echo json_encode([
            "reply" => "💰 Income options:",
            "options" => ["Total Income", "Last Income", "Big Income", "Low Income", "First Income"],
            "next" => "income"
        ]);
    } elseif ($choice === "Expense") {
        echo json_encode([
            "reply" => "💸 Expense options:",
            "options" => ["Total Expense", "Last Expense", "Big Expense", "Low Expense", "First Expense"],
            "next" => "expense"
        ]);
    } elseif ($choice === "Balance") {
        $income = getSum($con, "income", $user_id);
        $expense = getSum($con, "expenses", $user_id);
        $balance = $income - $expense;
        echo json_encode([
            "reply" => "💼 Your total balance is ₹" . number_format($balance, 2),
            "options" => ["Back to Main"],
            "next" => "main"
        ]);
    }
    exit;
}

if ($step === "income") {
    switch ($choice) {
        case "Total Income":
            $total = getSum($con, "income", $user_id);
            $reply = "💰 Total Income: ₹" . number_format($total, 2);
            break;
        case "Last Income":
            $res = $con->query("SELECT * FROM income WHERE User_id='$user_id' ORDER BY Income_date DESC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "🕒 Last Income: ₹{$row['Amount']} ({$row['Source']} - {$row['Income_date']})" : "No Income found.";
            break;
        case "Big Income":
            $res = $con->query("SELECT * FROM income WHERE User_id='$user_id' ORDER BY Amount DESC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "🏆 Highest Income: ₹{$row['Amount']} ({$row['Source']})" : "No Income found.";
            break;
        case "Low Income":
            $res = $con->query("SELECT * FROM income WHERE User_id='$user_id' ORDER BY Amount ASC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "⬇️ Lowest Income: ₹{$row['Amount']} ({$row['Source']})" : "No Income found.";
            break;
        case "First Income":
            $res = $con->query("SELECT * FROM income WHERE User_id='$user_id' ORDER BY Income_date ASC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "📅 First Income: ₹{$row['Amount']} ({$row['Source']} - {$row['Income_date']})" : "No Income found.";
            break;
        default:
            $reply = "Choose an option:";
    }
    echo json_encode(["reply" => $reply, "options" => ["Back"], "next" => "main"]);
    exit;
}

if ($step === "expense") {
    switch ($choice) {
        case "Total Expense":
            $total = getSum($con, "expenses", $user_id);
            $reply = "💸 Total Expense: ₹" . number_format($total, 2);
            break;
        case "Last Expense":
            $res = $con->query("SELECT * FROM expenses WHERE User_id='$user_id' ORDER BY Expense_date DESC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "🕒 Last Expense: ₹{$row['Amount']} ({$row['Description']} - {$row['Expense_date']})" : "No Expense found.";
            break;
        case "Big Expense":
            $res = $con->query("SELECT * FROM expenses WHERE User_id='$user_id' ORDER BY Amount DESC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "🔥 Biggest Expense: ₹{$row['Amount']} ({$row['Description']})" : "No Expense found.";
            break;
        case "Low Expense":
            $res = $con->query("SELECT * FROM expenses WHERE User_id='$user_id' ORDER BY Amount ASC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "🧊 Smallest Expense: ₹{$row['Amount']} ({$row['Description']})" : "No Expense found.";
            break;
        case "First Expense":
            $res = $con->query("SELECT * FROM expenses WHERE User_id='$user_id' ORDER BY Expense_date ASC LIMIT 1");
            $row = $res->fetch_assoc();
            $reply = $row ? "📅 First Expense: ₹{$row['Amount']} ({$row['Description']} - {$row['Expense_date']})" : "No Expense found.";
            break;
        default:
            $reply = "Choose an option:";
    }
    echo json_encode(["reply" => $reply, "options" => ["Back"], "next" => "main"]);
    exit;
}
?>
