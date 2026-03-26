<?php
session_start();
include "../db.php";

// Admin check
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin'){
    header("Location: ../index.php");
    exit();
}

// Check product_id
if(!isset($_GET['product_id'])){
    header("Location: displayproduct.php");
    exit();
}

$product_id = intval($_GET['product_id']);

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories");

// Message
$message = "";

// Handle update
if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_name = trim($_POST['category_name']);

    // Image handling
    $image = $product['image']; // keep old image
    if(isset($_FILES['image']) && $_FILES['image']['error']==0){
        $new_image = $_FILES['image']['name'];
        $temp = $_FILES['image']['tmp_name'];
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($new_image, PATHINFO_EXTENSION));

        if(in_array($ext,$allowed)){
            // Delete old image
            if(file_exists("../image/".$image)){
                unlink("../image/".$image);
            }
            move_uploaded_file($temp,"../image/".$new_image);
            $image = $new_image;
        } else {
            $message = "Invalid file type! Only JPG, PNG, GIF allowed.";
        }
    }

    // Update product with prepared statement
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=?, category_name=? WHERE id=?");
    $stmt->bind_param("ssdisii", $name, $description, $price, $stock, $image, $category_name, $product_id);

    if($stmt->execute()){
        $message = "Product updated successfully!";
        // Refresh product data
        $product['name']=$name;
        $product['description']=$description;
        $product['price']=$price;
        $product['stock']=$stock;
        $product['image']=$image;
        $product['category_name']=$category_name;
    } else {
        $message = "Error: ".$stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Product</title>
<style>
body{font-family:Arial;background:#f5f5f5;margin:0;padding:0;}
.dashboard_sidebar{position:fixed;top:0;left:0;width:200px;height:100%;background:darkcyan;padding-top:20px;}
.dashboard_sidebar ul{list-style:none;padding:0;}
.dashboard_sidebar ul li{margin-bottom:10px;}
.dashboard_sidebar ul li a{display:block;padding:10px 20px;color:white;text-decoration:none;}
.dashboard_sidebar ul li a:hover{background:cyan;}
.dashboard_main{margin-left:220px;padding:30px;}
form{background:white;padding:20px;border-radius:10px;max-width:600px;}
form input, form select, form textarea{width:100%;padding:10px;margin-bottom:15px;border:1px solid #ccc;border-radius:5px;}
form textarea{height:100px;resize:none;}
form input[type="submit"]{background:green;color:white;border:none;cursor:pointer;padding:10px;}
form input[type="submit"]:hover{background:#006400;}
img{width:150px;height:150px;object-fit:cover;margin-bottom:10px;}
.message{padding:10px;margin-bottom:15px;border-radius:5px;}
.success{background:#c8e6c9;color:#2e7d32;}
.error{background:#ffcdd2;color:#c62828;}
</style>
</head>
<body>
<div class="dashboard_sidebar">
<ul>
<li><a href="addproduct.php">Add Product</a></li>
<li><a href="displayproduct.php">View Products</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</div>

<div class="dashboard_main">
<h2>Update Product</h2>

<?php if($message!=""){ ?>
<div class="message <?php echo strpos($message,'success')!==false?'success':'error'; ?>">
    <?php echo htmlspecialchars($message); ?>
</div>
<?php } ?>

<form action="updateproduct.php?product_id=<?php echo $product_id ?>" method="post" enctype="multipart/form-data">
<label>Product Name</label>
<input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

<label>Description</label>
<textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>

<label>Price</label>
<input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>

<label>Stock</label>
<input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>

<label>Current Image</label>
<img src="../image/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">

<label>Change Image</label>
<input type="file" name="image">

<label>Category</label>
<select name="category_name" required>
<?php while($row = mysqli_fetch_assoc($categories)){ ?>
<option value="<?php echo htmlspecialchars($row['name']); ?>" <?php if($row['name']==$product['category_name']) echo 'selected'; ?>>
<?php echo htmlspecialchars($row['name']); ?>
</option>
<?php } ?>
</select>

<input type="submit" name="submit" value="Update Product">
</form>
</div>
</body>
</html>