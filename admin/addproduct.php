<?php
session_start();
include "../db.php";

/* ==============================
   ADMIN AUTH CHECK
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

/* ==============================
   FETCH CATEGORIES
================================ */
$result = mysqli_query($conn, "SELECT * FROM categories");
$message = "";

/* ==============================
   ADD PRODUCT LOGIC
================================ */
if (isset($_POST['submit'])) {

    $name          = trim($_POST['name']);
    $description   = trim($_POST['description']);
    $price         = (float) $_POST['price'];
    $stock         = (int) $_POST['stock'];
    $category_name = trim($_POST['category_name']);

    if (
        empty($name) || empty($price) ||
        empty($stock) || empty($category_name)
    ) {
        $message = "All fields are required.";
    }
    elseif (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        $message = "Please upload an image.";
    }
    else {

        /* ==============================
           IMAGE UPLOAD
        ================================ */
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $message = "Invalid image type!";
        }
        else {

            // create unique image name
            $image = time() . "_" . uniqid() . "." . $ext;
            $upload_dir = "../image/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            /* ==============================
               INSERT PRODUCT
            ================================ */
            $stmt = $conn->prepare(
              "INSERT INTO products 
              (name, description, price, stock, image, category_name)
               VALUES (?, ?, ?, ?, ?, ?)"
            );

            // ✅ CORRECT TYPES
            $stmt->bind_param(
                "ssdiss",
                $name,
                $description,
                $price,
                $stock,
                $image,
                $category_name
            );

            if ($stmt->execute()) {
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
                $message = "Product added successfully!";
            } else {
                $message = "Database error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial;}
body{display:flex;background:#f5f5f5;height:100vh;}
.dashboard_sidebar{
    width:220px;background:#00796b;color:white;
    height:100%;position:fixed;padding-top:20px
}
.dashboard_sidebar ul{list-style:none}
.dashboard_sidebar ul li a{
    display:block;color:white;text-decoration:none;
    padding:10px 20px
}
.dashboard_sidebar ul li a:hover{background:#004d40}
.dashboard_main{
    margin-left:240px;padding:30px;width:100%
}
form input, form textarea, form select{
    width:50%;padding:10px;margin-bottom:15px
}
form input[type=submit]{
    background:#00796b;color:white;border:none;padding:10px
}
.message.success{background:#c8e6c9;padding:10px}
.message.error{background:#ffcdd2;padding:10px}
</style>
</head>

<body>
<div class="dashboard_sidebar">
    <ul>
        <li><a href="addproduct.php">Add Product</a></li>
        <li><a href="vieworders.php">View Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard_main">
<h2>Add New Product</h2>

<?php if($message): ?>
<div class="message <?= strpos($message,'success')!==false?'success':'error' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product Name" required>
    <textarea name="description" placeholder="Description"></textarea>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <input type="number" name="stock" placeholder="Stock" required>
    <input type="file" name="image" required>

    <select name="category_name" required>
        <option value="">Select Category</option>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <option value="<?= htmlspecialchars($row['name']) ?>">
                <?= htmlspecialchars($row['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <input type="submit" name="submit" value="Add Product">
</form>
</div>
</body>
</html>
