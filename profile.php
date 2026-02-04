<?php
include 'session_check.php';
include 'config.php';
include 'dash.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT `Username`, `Email`, `Gender`, `Occupation` FROM `users` WHERE `User_id` = ?";
$stmt = $con->prepare($sql);
if (!$stmt) { die("Prepare failed: (" . $con->errno . ") " . $con->error); }

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
<title>Profile Page</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    margin: 0;
    padding: 0;
}

.profile-container {
    width: 100%;
    max-width: 600px; /* Desktop max width */
    margin: 30px auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.15);
    box-sizing: border-box;
    position: relative;
}

/* Top Buttons */
.top-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.settings-btn, .logout-btn {
    text-decoration: none;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-size: 14px;
    transition: 0.2s;
}

.settings-btn { background: #007BFF; }
.settings-btn:hover { background: #0056b3; }

.logout-btn { background: #f44336; }
.logout-btn:hover { background: #d32f2f; }

/* Profile Fields */
.profile-container h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 28px;
}
.profile-field {
    margin-bottom: 20px;
}
.profile-field label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    font-size: 16px;
}
.profile-field span {
    display: block;
    padding: 12px;
    background: #f0f0f0;
    border-radius: 6px;
    font-size: 16px;
}

/* Edit Profile Button */
.edit-btn {
    background: #4CAF50;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    border: none;
    font-size: 14px;
    cursor: pointer;
    transition: 0.2s;
}
.edit-btn:hover { background: #388E3C; }

.edit-container {
    text-align: center;
    margin-top: 30px;
}

/* ===== RESPONSIVE ===== */
@media(max-width: 650px){
    .profile-container {
        padding: 20px;
        margin: 20px auto;
    }

    .profile-container h2 {
        font-size: 24px;
    }

    .profile-field label, .profile-field span {
        font-size: 14px;
    }

    .edit-btn {
        width: 100%;
        padding: 10px 0;
        font-size: 14px;
    }

    .top-buttons {
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 25px;
    }
}

</style>
</head>
<body>
<div class="profile-container">
    <!-- Top Right Buttons -->
    <div class="top-buttons">
        <a href="settings.php" class="settings-btn">Settings</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    <br>
    <br>

    <h2>User Profile</h2>

    <div class="profile-field">
        <label>Username:</label>
        <span><?php echo htmlspecialchars($username); ?></span>
    </div>

    <div class="profile-field">
        <label>Email:</label>
        <span><?php echo htmlspecialchars($email); ?></span>
    </div>

    <div class="profile-field">
        <label>Gender:</label>
        <span><?php echo htmlspecialchars($gender); ?></span>
    </div>

    <div class="profile-field">
        <label>Occupation:</label>
        <span><?php echo htmlspecialchars($occupation); ?></span>
    </div>

    <!-- Edit Profile Button -->
    <div class="edit-container">
        <form action="edit_profile.php" method="get">
            <button type="submit" class="edit-btn">Edit Profile</button>
        </form>
    </div>
</div>
</body>
</html>
