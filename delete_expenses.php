<?php
session_start();
include("config.php");

if (isset($_POST['id'], $_POST['type'])) {
    $t_id = intval($_POST['id']);
$type = $_POST['type'];
    $user_id = $_SESSION['user_id'];

    if (strcasecmp($type, 'Income') === 0) { // case-insensitive
    $sql = "DELETE FROM income WHERE Income_id = ? AND user_id = ?";
} else if (strcasecmp($type, 'Expense') === 0) {
    $sql = "DELETE FROM expenses WHERE Expense_id = ? AND user_id = ?";
} else {
    echo "invalid_type"; exit;
}

    $stmt = $con->prepare($sql);
    if(!$stmt){ echo "Prepare error: " . $con->error; exit; }
    $stmt->bind_param("ii", $t_id, $user_id);
    if(!$stmt->execute()){ echo "Execute error: " . $stmt->error; exit; }

    if($stmt->affected_rows > 0){ echo "success"; }
    else { echo "not_found"; }

    $stmt->close();
} else {
    echo "missing_params";
}
