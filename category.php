<?php
include 'session_check.php';
include("config.php");
include 'dash.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Name'])) {
    $user_id = $_SESSION['user_id']; // Logged-in user
    $categoryName = trim($_POST['Name']); // Remove extra spaces

    if ($categoryName === '') {
        die("Category name cannot be empty");
    }

    // Insert category for this user
    $stmt = $con->prepare("INSERT INTO categories (Name, User_id) VALUES (?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $con->error);
    }

    $stmt->bind_param("si", $categoryName, $user_id);

    if ($stmt->execute()) {
        header("Location: categories.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    // Form not submitted
    // You can either redirect or just show the form
    // header("Location: categories.html");
}
?>

    <link rel="stylesheet" href="category.css">


    <main>

        <section>

            <div class="container">
                <h1>Add New Category</h1>

                <form id="addCategoryForm" action="category.php" method="post">
                    <label for="categoryName">Category Name:</label>
                    <input type="text" id="Name" name="Name" required>

                    <button type="submit" id="submit">Add Category</button>

                </form>

                <p class="back-link"><a href="#category" style="color: #696cff;">Back to Categories</a></p>

                <div id="errorMessage" class="error-message" style="display:none;"></div>
            </div>

        </section>

<?php
// include 'session_check.php';
// include("config.php");

if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id'];

// Fetch categories for the logged-in user
$sql = "SELECT Category_id, Name FROM categories WHERE User_id = ?";
$stmt = $con->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<section id="category" class="recent-transactions"
        style="max-width: 400px; margin-left: 38px;background-color: #fff;">
    <div class="t">
        <h2>Existing Categories</h2>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Categories</th>
                    <th style="padding-left: 34px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr data-category-id="<?php echo $row['Category_id']; ?>">
                        <td class="category-name"><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td class="actions">
                            <button class="edit-btn" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="delete-btn" title="Delete">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>







</body>


</main>


<!-- <script>
        document.getElementById('addCategoryForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const categoryNameInput = document.getElementById('categoryName');
            const categoryName = categoryNameInput.value.trim();
            const errorMessageDiv = document.getElementById('errorMessage');

            if (categoryName === "") {
                errorMessageDiv.textContent = "Category name cannot be empty.";
                errorMessageDiv.style.display = 'block';
                return;
            }

        
            console.log("Category Name submitted:", categoryName);
            alert(`Category "${categoryName}" would be added now.`);

            categoryNameInput.value = '';
            errorMessageDiv.style.display = 'none';

        });
    </script> -->
</body>

</html>