<?php
session_start();
include("config.php");

if(isset($_POST['id'], $_POST['type'], $_POST['date'], $_POST['desc'], $_POST['amount'])) {
    $t_id = intval($_POST['id']);
    $type = $_POST['type'];
    $date = $_POST['date'];
    $desc = $_POST['desc'];
    $amount = floatval($_POST['amount']);
    $user_id = $_SESSION['user_id'];

    if(strcasecmp($type, 'Income') === 0) {
        $sql = "UPDATE income SET Income_date=?, Source=?, Amount=? WHERE Income_id=? AND user_id=?";
    } else if(strcasecmp($type, 'Expense') === 0) {
        $sql = "UPDATE expenses SET Expense_date=?, Description=?, Amount=? WHERE Expense_id=? AND user_id=?";
    } else {
        echo "invalid_type"; exit;
    }

    $stmt = $con->prepare($sql);
    if(!$stmt){ echo "Prepare error: ".$con->error; exit; }
    $stmt->bind_param("ssdii", $date, $desc, $amount, $t_id, $user_id);

    if($stmt->execute() && $stmt->affected_rows > 0){
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
} else {
    echo "missing_params";
}
?>
