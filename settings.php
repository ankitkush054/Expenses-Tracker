<?php
session_start();
include 'config.php';
include 'dash.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 🧩 Change Password (normal way)
if(isset($_POST['current_password'], $_POST['new_password'])){
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];

    $stmt = $con->prepare("SELECT Password FROM users WHERE User_id = ?");
    $stmt->bind_param("i", $user_id);
    if(!$stmt->execute()){ die("Select execute error: ".$stmt->error); }
    $stmt->bind_result($old_password_db);
    $stmt->fetch();
    $stmt->close();

    if($current === $old_password_db){
        $stmt = $con->prepare("UPDATE users SET Password = ? WHERE User_id = ?");
        $stmt->bind_param("si", $new, $user_id);
        if($stmt->execute()){
            $message = "Password changed successfully!";
        } else {
            $message = "Error updating password: ".$stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Current password is incorrect!";
    }
}

// 🧩 Forgot Password Process
if(isset($_POST['forgot_username'], $_POST['forgot_email'], $_POST['new_forgot_password'])){
    $uname = trim($_POST['forgot_username']);
    $email = trim($_POST['forgot_email']);
    $new_pass = trim($_POST['new_forgot_password']);

    $stmt = $con->prepare("SELECT User_id FROM users WHERE Username = ? AND Email = ?");
    $stmt->bind_param("ss", $uname, $email);
    $stmt->execute();
    $stmt->bind_result($found_user);
    $stmt->fetch();
    $stmt->close();

    if($found_user){
        $stmt = $con->prepare("UPDATE users SET Password = ? WHERE User_id = ?");
        $stmt->bind_param("si", $new_pass, $found_user);
        if($stmt->execute()){
            $message = "✅ Password reset successful!";
        } else {
            $message = "❌ Error resetting password!";
        }
        $stmt->close();
    } else {
        $message = "⚠️ Username or Email not found!";
    }
}

// 🧩 Delete Account
if(isset($_POST['delete_account'])){
    $stmt = $con->prepare("DELETE FROM users WHERE User_id = ?");
    $stmt->bind_param("i", $user_id);
    if($stmt->execute()){
        session_destroy();
        header("Location: index.html");
        exit();
    } else {
        $message = "Error deleting account!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    color: #333;
    margin: 0;
    padding: 0;
}
.container {
    width: 100%;
    max-width: 500px;
    margin: 30px auto;
    padding: 25px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    box-sizing: border-box;
}
h2 { text-align: center; margin-bottom: 25px; }
button {
    margin: 10px 5px;
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    background: #007BFF;
    color: white;
    font-size: 14px;
    transition: 0.2s;
}
button:hover { background: #0056b3; }
button.delete-btn { background: #f44336; }
button.delete-btn:hover { background: #c62828; }
button.alt-btn { background: #6c757d; }
button.alt-btn:hover { background: #5a6268; }
.message {
    margin: 15px 0;
    padding: 10px;
    background: #d4edda;
    color: #155724;
    border-radius: 4px;
}
.hidden { display: none; }
input[type="password"], input[type="text"], input[type="email"] {
    width: 100%;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    margin-bottom: 10px;
    font-size: 14px;
    box-sizing: border-box;
}
@media (max-width: 768px) {
    .container { padding: 20px; margin: 20px auto; }
    button { width: 100%; margin-bottom: 10px; }
}
@media (max-width: 480px) {
    .container { padding: 15px; margin: 15px auto; }
    h2 { font-size: 20px; }
    button { font-size: 13px; padding: 8px 0; }
    input { padding: 8px; font-size: 13px; }
}
</style>
</head>
<body>
<div class="container">
    <h2>Settings</h2>

    <?php if($message): ?>
        <div class="message" id="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <script>
    window.addEventListener('DOMContentLoaded', () => {
        const msg = document.getElementById('message');
        if(msg){ setTimeout(() => msg.style.display = 'none', 3000); }
    });
    </script>

    <div id="choices">
        <button id="changePasswordBtn">Change Password</button>
        <button id="deleteAccountBtn">Delete Account</button>
    </div>

    <!-- 🔐 Change Password Form -->
    <form id="passwordForm" method="post" class="hidden">
        <label>Current Password:</label>
        <input type="password" name="current_password" required>

        <label>New Password:</label>
        <input type="password" name="new_password" required>

        <button type="submit">Update Password</button>
        <button type="button" id="forgotPasswordBtn" class="alt-btn">Forgot Password?</button>
        <button type="button" id="cancelPassword">Cancel</button>
    </form>

    <!-- 🔑 Forgot Password Form -->
    <form id="forgotForm" method="post" class="hidden">
        <label>Enter Username:</label>
        <input type="text" name="forgot_username" required>

        <label>Enter Email:</label>
        <input type="email" name="forgot_email" required>

        <label>New Password:</label>
        <input type="password" name="new_forgot_password" required>

        <button type="submit">Reset Password</button>
        <button type="button" id="cancelForgot" class="alt-btn">Cancel</button>
    </form>

    <!-- 🗑️ Delete Account -->
    <form id="deleteForm" method="post" class="hidden" onsubmit="return confirm('Are you sure you want to delete your account?');">
        <input type="hidden" name="delete_account" value="1">
        <button type="submit" class="delete-btn">Yes, Delete My Account</button>
        <button type="button" id="cancelDelete">Cancel</button>
    </form>
</div>

<script>
// Password form open
document.getElementById('changePasswordBtn').onclick = () => {
    document.getElementById('passwordForm').classList.remove('hidden');
    document.getElementById('deleteForm').classList.add('hidden');
};
// Delete form open
document.getElementById('deleteAccountBtn').onclick = () => {
    document.getElementById('deleteForm').classList.remove('hidden');
    document.getElementById('passwordForm').classList.add('hidden');
};
// Cancel buttons
document.getElementById('cancelPassword').onclick = () => {
    document.getElementById('passwordForm').classList.add('hidden');
};
document.getElementById('cancelDelete').onclick = () => {
    document.getElementById('deleteForm').classList.add('hidden');
};
// Forgot password button
document.getElementById('forgotPasswordBtn').onclick = () => {
    document.getElementById('passwordForm').classList.add('hidden');
    document.getElementById('forgotForm').classList.remove('hidden');
};
// Cancel forgot
document.getElementById('cancelForgot').onclick = () => {
    document.getElementById('forgotForm').classList.add('hidden');
    document.getElementById('passwordForm').classList.remove('hidden');
};
</script>
</body>
</html>
