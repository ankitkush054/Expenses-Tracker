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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $occupation = $_POST['occupation'];

    // Update basic profile info
    $stmt = $con->prepare("UPDATE users SET Username = ?, Email = ?, Gender = ?, Occupation = ? WHERE User_id = ?");
    $stmt->bind_param("ssssi", $username, $email, $gender, $occupation, $user_id);
    if(!$stmt->execute()){
        $message = "Error updating profile: " . $stmt->error;
    }
    $stmt->close();

    // Handle password change if fields are filled
    if(!empty($_POST['current_password']) && !empty($_POST['new_password'])){
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];

        // Fetch old password from DB (plain text)
        $stmt = $con->prepare("SELECT Password FROM users WHERE User_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($old_password_db);
        $stmt->fetch();
        $stmt->close();

        if($current === $old_password_db){
            // Update new password
            $stmt = $con->prepare("UPDATE users SET Password = ? WHERE User_id = ?");
            $stmt->bind_param("si", $new, $user_id);
            if($stmt->execute()){
                $message .= "<br>Password changed successfully!";
            } else {
                $message .= "<br>Error updating password: ".$stmt->error;
            }
            $stmt->close();
        } else {
            $message .= "<br>Current password is incorrect!";
        }
    }

    // If no errors, redirect to profile.php
    if(empty($message)){
        header("Location: profile.php");
        exit();
    }
}

// Fetch current user data
$stmt = $con->prepare("SELECT Username, Email, Gender, Occupation FROM users WHERE User_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $gender, $occupation);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
.container { width: 600px; max-width: 90%; margin: 50px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.15); }
h2 { text-align: center; margin-bottom: 25px; }
label { display: block; margin: 10px 0 5px; font-weight: bold; }
input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
button { margin-top: 15px; padding: 10px 20px; border-radius: 5px; border: none; background: #4CAF50; color: white; cursor: pointer; font-size: 14px; }
button:hover { background: #388E3C; }
.message { margin: 15px 0; padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; }
.cancel-btn { background: #f44336; margin-left: 10px; }
.cancel-btn:hover { background: #d32f2f; }
</style>
</head>
<body>
<div class="container">
    <h2>Edit Profile</h2>

    <?php if($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label>Gender:</label>
        <select name="gender" required>
            <option value="Male" <?php if($gender === 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($gender === 'Female') echo 'selected'; ?>>Female</option>
            <option value="Other" <?php if($gender === 'Other') echo 'selected'; ?>>Other</option>
        </select>

        <label>Occupation:</label>
        <input type="text" name="occupation" value="<?php echo htmlspecialchars($occupation); ?>" required>

        <hr style="margin:20px 0;">

        <label>Current Password (for change):</label>
        <input type="password" name="current_password">

        <label>New Password:</label>
        <input type="password" name="new_password">

        <div style="margin-top: 20px; text-align:center;">
            <button type="submit">Update Profile</button>
            <a href="profile.php"><button type="button" class="cancel-btn">Cancel</button></a>
        </div>
    </form>
</div>
</body>
</html>
