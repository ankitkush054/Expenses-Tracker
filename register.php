<?php
include("config.php");

$message = ""; // For popup message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['Username']);
    $e = trim($_POST['Email']);
    $p = trim($_POST['Password']);
    $g = $_POST['Gender'] ?? '';
    $o = trim($_POST['Occupation']);

   // 0️⃣ Password validation: minimum 8 characters
if (strlen($p) < 8) {
    $message = "Password must be at least 8 characters long.";
}

    // 1️⃣ Gmail validation
    if (!filter_var($e, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\.com$/", $e)) {
        $message = "Please enter a valid Gmail address (example@gmail.com).";
    } else {
        // 2️⃣ Check if username exists
        $stmt = $con->prepare("SELECT User_id FROM users WHERE Username=?");
        $stmt->bind_param("s", $n);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Username already taken. Please choose a different one.";
        }
        $stmt->close();

        // 3️⃣ Check if email exists
        $stmt = $con->prepare("SELECT User_id FROM users WHERE Email=?");
        $stmt->bind_param("s", $e);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Email already registered. Please use a different Gmail address.";
        }
        $stmt->close();
    }

    // 4️⃣ Insert user if no errors
    if ($message === "") {
        $stmt = $con->prepare("INSERT INTO users (Username, Email, Password, Gender, Occupation) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $n, $e, $p, $g, $o);
        if ($stmt->execute()) {
    session_start(); // Agar session start nahi hua ho to
    $_SESSION['user_id'] = $stmt->insert_id; // Naya user ka ID session me store karo
    $_SESSION['username'] = $n; // Optional: username bhi store kar sakte ho
    echo "<script>alert('Account created successfully!'); window.location='dashboard.php';</script>";
    exit();


        } else {
            $message = "Failed to insert user: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!doctype html>

<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default"
  data-assets-path="../assets/" data-template="vertical-menu-template-free" data-style="light">

<head><meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />

  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Expensio Registration Pages </title>
  <link rel="stylesheet" href="register.css">


  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <script src="../assets/js/config.js"></script>
</head>

<body>
  <!-- Content -->
  <!-- <img src="img/dot.png" alt="" style="height: 100px;
  margin-left: 900px;
  margin-top: 80px;"> -->
 <?php
    if (!empty($message)) {
        echo "<script>alert('".$message."');</script>";
    }
  ?>
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register Card -->
        <div class="card px-sm-6 px-0">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-6">
              <a href="dashboard.php" class="app-brand-link gap-2">
                <img src="img/expense-removebg-preview.png" alt="" srcset="" style="height: 50px;align-items: center;">
                <span class="app-brand-text demo text-heading fw-bold">Expensio</span>
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1">Expense tracking starts here 🚀</h4>
            <p class="mb-6">Make your expense management easy!</p>

            <form id="formAuthentication" class="mb-6" action="register.php" method="post">
              <div class="mb-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="Username" name="Username" placeholder="Enter your username"
                  autofocus />
              </div>
              <div class="mb-6">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="Email" name="Email" placeholder="Enter your email" />
              </div>
              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="Password" class="form-control" name="Password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
                <br>
                <div class="mb-6">
                  <label for="" class="form-label">Gender</label>
                  <br>
                  <label class="radio-label">
                    <input type="radio" name="Gender" value="male">
                    <span class="radio-custom"></span>
                    Male
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="Gender" value="female">
                    <span class="radio-custom"></span>
                    Female
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="Gender" value="other">
                    <span class="radio-custom"></span>
                    Other
                  </label>
                </div>

                <div class="mb-6">
                  <label for="Occupation" class="form-label">Occupation</label>
                  <input type="text" class="form-control" id="Occupation" name="Occupation"
                    placeholder="Enter your occupation" />
                </div>
              </div>

              <div class="my-8">
                <div class="form-check mb-0 ms-2">
                  <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                  <label class="form-check-label" for="terms-conditions">
                    I agree to
                    <a href="javascript:void(0);">privacy policy & terms</a>
                  </label>
                </div>
              </div>

              <button class="btn btn-primary d-grid w-100" id="submit">Sign up</button>
            </form>

            <p class="text-center">
              <span>Already have an account?</span>
              <a href="login.html">
                <span>Sign in instead</span>
              </a>
            </p>
          </div>
        </div>
        <!-- Register Card -->
      </div>
    </div>
  </div>

  <script src="../assets/vendor/libs/jquery/jquery.js"></script>
  <script src="../assets/vendor/libs/popper/popper.js"></script>
  <script src="../assets/vendor/js/bootstrap.js"></script>
  <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="../assets/vendor/js/menu.js"></script>

  <!-- endbuild -->

  <!-- Vendors JS -->

  <!-- Main JS -->
  <script src="../assets/js/main.js"></script>

  <!-- Page JS -->

  <!-- Place this tag before closing body tag for github widget button. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>