<?php
session_start();
include("config.php");

$message = "";

// Step 1: Check if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);

    if (empty($username) || empty($email) || empty($new_password)) {
        $message = "⚠️ Please fill in all fields.";
    } else {
        $stmt = $con->prepare("SELECT User_id FROM users WHERE Username = ? AND Email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id);
            $stmt->fetch();

            // (Optional secure password)
            // $new_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt2 = $con->prepare("UPDATE users SET Password = ? WHERE User_id = ?");
            $stmt2->bind_param("si", $new_password, $user_id);
            if ($stmt2->execute()) {
                // ✅ Redirect to login page with success message
                header("Location: login.php?reset=success");
                exit();
            } else {
                $message = "❌ Error updating password.";
            }
            $stmt2->close();
        } else {
            $message = "❌ No account found with that username and email.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Forgot Password | Expensio</title>

    <link rel="stylesheet" href="login.css">
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700&display=swap"
        rel="stylesheet" />
    <style>
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: 500;
            color: #fff;
            padding: 8px;
            border-radius: 6px;
        }
        .success { background: #4CAF50; }
        .error { background: #f44336; }
    </style>
</head>

<body>
    <div class="container-xxl">
        <div class="authentication-wrapper">
            <div class="authentication-inner">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="dashboard.php" class="app-brand-link gap-2">
                                <img src="img/expense-removebg-preview.png" alt="" style="height: 50px;">
                                <span class="app-brand-text demo text-heading fw-bold">Expensio</span>
                            </a>
                        </div>
                        <h4 class="mb-1">Forgot Password 🔑</h4>
                        <p class="mb-6">Enter your username, email and set a new password</p>

                        <?php if (!empty($message)): ?>
                            <div class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <form id="forgotForm" action="forgot.php" method="POST">
                            <div class="mb-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                            </div>

                            <div class="mb-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Enter your registered email" required>
                            </div>

                            <div class="mb-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Enter new password" required>
                            </div>

                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100" type="submit">Reset Password</button>
                            </div>
                        </form>

                        <p class="text-center">
                            <a href="login.php"><span>Back to Login</span></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
