<?php
session_start();
include "../db.php";

// Check if user is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin'){
    header("Location: ../index.php");
    exit();
}

// Fetch categories
$sql = "SELECT * FROM categories";
$result = mysqli_query($conn, $sql);

$message = ""; // success or error message

if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $category_name = trim($_POST['category_name']);

    // File upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $image = $_FILES['image']['name'];
        $temp_location = $_FILES['image']['tmp_name'];
        $upload_location = "../image/";

        // Validate file type
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed)){
            $message = "Invalid file type! Only JPG, PNG, GIF allowed.";
        } else {
            // Use prepared statement to insert
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category_name) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssddss", $name, $description, $price, $stock, $image, $category_name);

            if($stmt->execute()){
                move_uploaded_file($temp_location, $upload_location.$image);
                $message = "Product added successfully!";
            } else {
                $message = "Error: ".$stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Please upload an image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Arial;}
body{display:flex;background:#f5f5f5;height:100vh;}
.dashboard_sidebar{
    width:220px;background:#00796b;color:white;height:100%;position:fixed;padding-top:20px;
}
.dashboard_sidebar ul{list-style:none;}
.dashboard_sidebar ul li{margin-bottom:10px;}
.dashboard_sidebar ul li a{
    display:block;color:white;text-decoration:none;padding:10px 20px;border-radius:5px;
}
.dashboard_sidebar ul li a:hover{background:#004d40;}
.dashboard_main{
    margin-left:240px;padding:30px;width:100%;
}
.dashboard_main h2{margin-bottom:20px;color:#00796b;}
form input, form select, form textarea{
    width:50%;padding:10px;margin-bottom:15px;border:1px solid #ccc;border-radius:5px;
}
form textarea{height:80px;}
form input[type="submit"]{
    background:#00796b;color:white;border:none;padding:10px 20px;cursor:pointer;border-radius:5px;
}
form input[type="submit"]:hover{background:#004d40;}
.message{
    margin-bottom:15px;padding:10px;border-radius:5px;
}
.success{background:#c8e6c9;color:#2e7d32;}
.error{background:#ffcdd2;color:#c62828;}
@media(max-width:768px){
    .dashboard_main form input, .dashboard_main form select, .dashboard_main form textarea{width:100%;}
}
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

    <?php if($message!=""){ ?>
        <div class="message <?php echo strpos($message,'success')!==false ? 'success':'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>

    <form action="addproduct.php" method="post" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required>
        <textarea name="description" placeholder="Product Description"></textarea>
        <input type="number" name="price" placeholder="Product Price" step="0.01" required>
        <input type="number" name="stock" placeholder="Stock Quantity" required>
        <input type="file" name="image" required>
        <select name="category_name" required>
            <option value="">Select Category</option>
            <?php while($row = mysqli_fetch_assoc($result)) { ?>
                <option value="<?php echo htmlspecialchars($row['name']); ?>">
                    <?php echo htmlspecialchars($row['name']); ?>
                </option>
            <?php } ?>
        </select>
        <input type="submit" name="submit" value="Add Product">
    </form>
</div>
</body>
</html>