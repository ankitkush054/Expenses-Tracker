
<?php
ob_start(); // start output buffering
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expensio | Dashboard </title>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="preconnect" href="https://fonts.googleapis.com">

  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">


  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>


  <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet">



  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">


  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>


  <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="landing.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> -->


  <link rel="stylesheet" href="dashboard.css" />
  <link rel="stylesheet" href="dchart.css">
  <link rel="stylesheet" href="dcard.css">
  <link rel="stylesheet" href="dbutton.css">
  <link rel="stylesheet" href="dtransaction.css">

<script type="text/javascript">
  function preventBack(){window.history.forword()};
  setTimeout("preventBack",0);
  window.onunload=function(){null};

</script>

</head>

<body>





  <aside class="sidebar">
    <div class="sidebar-header">
    <a href="dashboard.php" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
        <img src="img/expense-removebg-preview.png" alt="logo" style="height: 50px; margin-right: 10px;" />
        <h2 style="margin: 0;">Expensio</h2>
    </a>
</div>


    <ul class="sidebar-links">
      <!-- <h4>
        <span>Main Menu</span>
        <div class="menu-separator"></div>
      </h4> -->
      <li>
        <a href="dashboard.php">
            <span class="material-symbols-outlined" style="color: #434343;"> dashboard </span>Dashboard
        </a>
      </li>

      <li>
        <a href="add_income.php"> <span class="material-symbols-outlined" style="color: #434343;">
            account_balance_wallet
          </span>Add Income</a>
      </li>

      <li>
        <a href="add_expense.php"><span class="material-symbols-outlined" style="color: #434343;">
            add_card
          </span>Add Expense</a>
      </li>

      <li>
        <a href="dashboard.php#Transaction"><span class="material-symbols-outlined" style="color: #434343;">
            receipt_long
          </span>Transaction</a>
      </li>
      <li>
       <li>
  <a href="chart.php">
    <span class="material-symbols-outlined" style="color: #434343;">
      bar_chart
    </span>
    Charts
  </a>
</li>
 <li>
    <a href="download_report.php">
      <span class="material-symbols-outlined" style="color: #434343;">download</span>
      Download Report
    </a>
  </li>

      <!-- <li>
        <a href="category.php"><span class="material-symbols-outlined" style="color: #434343;">
                        category_search
                    </span>Categories</a>
      </li> -->

      <!-- <li>
        <a href="report.php"><span class="material-symbols-outlined" style="color: #434343;">bar_chart</span>Reports &
          Analytics</a>
      </li> -->


      <li>
        <a href="profile.php"><span class="material-symbols-outlined" style="color: #434343;"> account_circle
          </span>Profile</a>
      </li>

      <li>
        <a href="settings.php"><span class="material-symbols-outlined" style="color: #434343;"> settings
          </span>Settings</a>
      </li>

   <li>
  <a href="logout.php">
    <span class="material-symbols-outlined" style="color: #434343; padding-bottom: 0px;">
      logout
    </span>
    Logout
  </a>
</li>


    </ul>
    
    </div>

  </aside>