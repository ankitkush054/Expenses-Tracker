<?php
if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    echo "<script>
        window.onload = function() {
            alert('✅ Password reset successfully! Please login.');
        };
    </script>";
}
?>


<?php
session_start();
include("config.php");

// If user already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Disable caching
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

$u = isset($_POST['Username']) ? trim($_POST['Username']) : '';
$p = isset($_POST['Password']) ? $_POST['Password'] : '';

if ($u === '' || $p === '') {
    echo "<script>alert('Please enter both username and password'); window.location.href='login.html';</script>";
    exit();
}

$stmt = $con->prepare("SELECT User_id, Username, Password FROM users WHERE Username = ?");
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("s", $u);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result === false) {
    die("Get result failed: " . $stmt->error);
}

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $dbHash = $row['Password'];
    $passwordOK = false;

    if (password_verify($p, $dbHash)) {
        $passwordOK = true;
    } elseif ($p === $dbHash) { 
        $passwordOK = true;
    }

    if ($passwordOK) {
        session_regenerate_id(true); 
        $_SESSION['user_id'] = $row['User_id'];
        $_SESSION['Username'] = $row['Username'];

        header("Location: dashboard.php");
        exit();
    } else {
        // Password incorrect
        echo "<script>alert('Incorrect password. Please try again.'); window.location.href='login.html';</script>";
        exit();
    }
} else {
    // Username not found
    echo "<script>alert('Username not found. Please check your username.'); window.location.href='login.html';</script>";
    exit();
}
?>
