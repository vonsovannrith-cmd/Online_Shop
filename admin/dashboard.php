<?php
session_start();
include "../db.php";

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'add';
$message = "";
$message_type = "";

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories");

// Helper function for unique image name
function uploadImage($file, $upload_dir)
{
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($ext, $allowed)) {
        return ["", "Invalid image type! Only JPG, PNG, GIF allowed."];
    }

    $new_name = time() . "_" . rand(1000, 9999) . "." . $ext;
    $target = $upload_dir . $new_name;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return [$new_name, ""];
    }

    return ["", "Image upload failed!"];
}

// ------------------ HANDLE ADD PRODUCT ------------------
if ($page == 'add' && isset($_POST['add_product'])) {

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_name = trim($_POST['category_name']);

    if ($name == "" || $price <= 0 || $stock < 0 || $category_name == "") {
        $message = "Please fill all required fields correctly!";
        $message_type = "error";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        $message = "Please upload an image!";
        $message_type = "error";
    } else {
        $upload_dir = "../image/";

        list($image, $error) = uploadImage($_FILES['image'], $upload_dir);

        if ($error != "") {
            $message = $error;
            $message_type = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category_name) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $image, $category_name);

            if ($stmt->execute()) {
                $message = "Product added successfully!";
                $message_type = "success";
            } else {
                $message = "Error adding product: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

// ------------------ HANDLE DELETE PRODUCT ------------------
if ($page == 'view' && isset($_GET['delete_id'])) {

    $delete_id = intval($_GET['delete_id']);

    // Get image name first
    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $image_path = "../image/" . $row['image'];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    $stmt->close();

    // Delete product row
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?page=view&msg=deleted");
        exit();
    }
    $stmt->close();
}

// ------------------ HANDLE INLINE UPDATE ------------------
if ($page == 'view' && isset($_POST['update_product'])) {

    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_name = trim($_POST['category_name']);

    if ($name == "" || $price <= 0 || $stock < 0 || $category_name == "") {
        $message = "Invalid input! Please check fields.";
        $message_type = "error";
    } else {

        // Get old image
        $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row_img = $res->fetch_assoc();
        $old_image = $row_img['image'];
        $stmt->close();

        $image = $old_image;

        // If new image uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

            $upload_dir = "../image/";
            list($new_image, $error) = uploadImage($_FILES['image'], $upload_dir);

            if ($error != "") {
                $message = $error;
                $message_type = "error";
            } else {
                if ($old_image != "" && file_exists("../image/" . $old_image)) {
                    unlink("../image/" . $old_image);
                }
                $image = $new_image;
            }
        }

        if ($message_type != "error") {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=?, category_name=? WHERE id=?");
            $stmt->bind_param("ssdissi", $name, $description, $price, $stock, $image, $category_name, $id);

            if ($stmt->execute()) {
                $message = "Product updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error updating product: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

// ------------------ FETCH PRODUCTS ------------------
if ($page == 'view') {
    $products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial;}
body{display:flex;background:#f5f5f5;transition:0.3s;}

/* DARK MODE */
body.dark{
    background:#111827;
    color:white;
}

/* Sidebar */
.dashboard_sidebar{
    width:220px;
    background:#00796b;
    color:white;
    height:100vh;
    position:fixed;
    padding-top:20px;
    transition:0.3s;
}

body.dark .dashboard_sidebar{
    background:#0f172a;
}

.dashboard_sidebar ul{list-style:none;}
.dashboard_sidebar ul li{margin-bottom:10px;}
.dashboard_sidebar ul li a{
    display:block;
    color:white;
    text-decoration:none;
    padding:10px 20px;
    border-radius:5px;
    font-weight:bold;
}
.dashboard_sidebar ul li a:hover{background:#004d40;}

body.dark .dashboard_sidebar ul li a:hover{
    background:#1e293b;
}

/* Main */
.dashboard_main{
    margin-left:240px;
    padding:20px;
    width:100%;
}

/* Top Header */
.top-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

/* Dark Mode Button */
.dark-btn{
    padding:8px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
    background:#facc15;
    transition:0.3s;
}

body.dark .dark-btn{
    background:#2563eb;
    color:white;
}

/* Form */
form input, form select, form textarea{
    width:95%;
    padding:8px;
    margin-bottom:8px;
    border:1px solid #ccc;
    border-radius:6px;
    transition:0.3s;
}

body.dark form input,
body.dark form select,
body.dark form textarea{
    background:#1f2937;
    color:white;
    border:1px solid #374151;
}

form textarea{height:60px;resize:none;}

form input[type="submit"]{
    background:#00796b;
    color:white;
    border:none;
    padding:8px 15px;
    cursor:pointer;
    border-radius:6px;
    font-weight:bold;
}
form input[type="submit"]:hover{background:#004d40;}

/* Messages */
.message{
    margin:15px 0;
    padding:12px;
    border-radius:6px;
    font-weight:bold;
}
.success{background:#c8e6c9;color:#2e7d32;}
.error{background:#ffcdd2;color:#c62828;}

body.dark .success{
    background:#14532d;
    color:#bbf7d0;
}

body.dark .error{
    background:#7f1d1d;
    color:#fecaca;
}

/* Table */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:white;
    transition:0.3s;
}

body.dark table{
    background:#1f2937;
}

table, th, td{border:1px solid #ddd;}

body.dark table, 
body.dark th, 
body.dark td{
    border:1px solid #374151;
}

th{background:#00796b;color:white;padding:10px;}
td{padding:8px;text-align:center;}

body.dark th{
    background:#0f766e;
}

img{width:70px;height:60px;object-fit:cover;border-radius:6px;}

/* Buttons */
.delete-btn{
    background:red;
    color:white;
    padding:6px 12px;
    text-decoration:none;
    border-radius:6px;
    font-weight:bold;
}
.delete-btn:hover{background:darkred;}
</style>

</head>
<body>

<div class="dashboard_sidebar">
    <ul>
        <li><a href="dashboard.php?page=add">➕ Add Product</a></li>
        <li><a href="dashboard.php?page=view">📦 View Products</a></li>
        <li><a href="../logout.php">🚪 Logout</a></li>
    </ul>
</div>

<div class="dashboard_main">

    <div class="top-header">
        <h2>Admin Dashboard</h2>

        <!-- DARK MODE BUTTON -->
        <button class="dark-btn" onclick="toggleDarkMode()" id="darkBtn">🌙 Dark</button>
    </div>

<?php if ($page == 'add') { ?>

    <h2>Add New Product</h2>

    <?php if ($message != "") { ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <form action="dashboard.php?page=add" method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required>
        <textarea name="description" placeholder="Product Description"></textarea>
        <input type="number" name="price" placeholder="Product Price" step="0.01" required>
        <input type="number" name="stock" placeholder="Stock Quantity" required>
        <input type="file" name="image" required>

        <select name="category_name" required>
            <option value="">Select Category</option>
            <?php
            mysqli_data_seek($categories, 0);
            while ($row = mysqli_fetch_assoc($categories)) { ?>
                <option value="<?php echo htmlspecialchars($row['name']); ?>">
                    <?php echo htmlspecialchars($row['name']); ?>
                </option>
            <?php } ?>
        </select>

        <input type="submit" name="add_product" value="Add Product">
    </form>

<?php } elseif ($page == 'view') { ?>

    <h2>All Products</h2>

    <?php if ($message != "") { ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <table>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image</th>
            <th>Category</th>
            <th>Update</th>
            <th>Delete</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($products)) { ?>
        <tr>
            <form action="dashboard.php?page=view" method="post" enctype="multipart/form-data">
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
                <td><textarea name="description"><?php echo htmlspecialchars($row['description']); ?></textarea></td>
                <td><input type="number" name="price" value="<?php echo $row['price']; ?>" step="0.01"></td>
                <td><input type="number" name="stock" value="<?php echo $row['stock']; ?>"></td>

                <td>
                    <img src="../image/<?php echo htmlspecialchars($row['image']); ?>" alt="product">
                    <input type="file" name="image">
                </td>

                <td>
                    <select name="category_name">
                        <?php
                        mysqli_data_seek($categories, 0);
                        while ($cat = mysqli_fetch_assoc($categories)) { ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>"
                                <?php if ($cat['name'] == $row['category_name']) echo "selected"; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>

                <td>
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="submit" name="update_product" value="Save">
                </td>

                <td>
                    <a class="delete-btn"
                       href="dashboard.php?page=view&delete_id=<?php echo $row['id']; ?>"
                       onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </form>
        </tr>
        <?php } ?>
    </table>

<?php } ?>

</div>

<script>
function toggleDarkMode(){
    document.body.classList.toggle("dark");

    if(document.body.classList.contains("dark")){
        localStorage.setItem("darkMode", "enabled");
        document.getElementById("darkBtn").innerHTML = "☀️ Light";
    } else {
        localStorage.setItem("darkMode", "disabled");
        document.getElementById("darkBtn").innerHTML = "🌙 Dark";
    }
}

window.onload = function(){
    if(localStorage.getItem("darkMode") === "enabled"){
        document.body.classList.add("dark");
        document.getElementById("darkBtn").innerHTML = "☀️ Light";
    }
}
</script>

</body>
</html>