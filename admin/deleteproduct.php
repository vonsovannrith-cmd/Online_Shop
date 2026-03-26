<?php
session_start();
include "../db.php";

// Check login
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin'){
    header("Location: ../index.php");
    exit();
}

// Check if product_id is set
if(!isset($_GET['product_id'])){
    header("Location: displayproduct.php");
    exit();
}

$product_id = $_GET['product_id'];

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Fetch categories
$categories = mysqli_query($conn, "SELECT * FROM categories");

// Message variable
$message = "";

// Handle form submission
if(isset($_POST['submit'])){
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $category_name = trim($_POST['category_name']);

    // Image handling
    $image = $product['image']; // keep existing image by default
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $new_image = $_FILES['image']['name'];
        $temp_location = $_FILES['image']['tmp_name'];
        $upload_location = "../image/";

        // Validate file type
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($new_image, PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)){
            // Delete old image
            if(file_exists($upload_location.$image)){
                unlink($upload_location.$image);
            }
            $image = $new_image;
            move_uploaded_file($temp_location, $upload_location.$image);
        } else {
            $message = "Invalid file type! Only JPG, PNG, GIF allowed.";
        }
    }

    // Update product using prepared statement
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=?, category_name=? WHERE id=?");
    $stmt->bind_param("ssddssi", $name, $description, $price, $stock, $image, $category_name, $product_id);

    if($stmt->execute()){
        $message = "Product updated successfully!";
        // Refresh product info
        $product['name'] = $name;
        $product['description'] = $description;
        $product['price'] = $price;
        $product['stock'] = $stock;
        $product['image'] = $image;
        $product['category_name'] = $category_name;
    } else {
        $message = "Error updating product: " . $stmt->error;
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
body{font-family: Arial; background:#f5f5f5; padding:20px;}
h2{color:#00796b;margin-bottom:20px;}
form{background:white;padding:20px; border-radius:10px; width:50%;}
form input, form textarea, form select{width:100%;padding:10px;margin-bottom:15px;border:1px solid #ccc;border-radius:5px;}
form textarea{height:80px;}
form input[type="submit"]{background:#00796b;color:white;border:none;padding:10px 20px;cursor:pointer;border-radius:5px;}
form input[type="submit"]:hover{background:#004d40;}
img{max-width:150px; display:block;margin-bottom:10px;}
.message{margin-bottom:15px;padding:10px;border-radius:5px;}
.success{background:#c8e6c9;color:#2e7d32;}
.error{background:#ffcdd2;color:#c62828;}
</style>
</head>
<body>

<h2>Update Product</h2>

<?php if($message!=""){ ?>
    <div class="message <?php echo strpos($message,'success')!==false ? 'success':'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php } ?>

<form action="" method="post" enctype="multipart/form-data">
    <label>Product Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

    <label>Description</label>
    <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>

    <label>Price</label>
    <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>

    <label>Stock</label>
    <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>

    <label>Current Image</label>
    <img src="../image/<?php echo htmlspecialchars($product['image']); ?>" alt="product image">

    <label>Change Image</label>
    <input type="file" name="image">

    <label>Category</label>
    <select name="category_name" required>
        <?php while($cat = mysqli_fetch_assoc($categories)){ ?>
            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php if($cat['name']==$product['category_name']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php } ?>
    </select>

    <input type="submit" name="submit" value="Update Product">
</form>

</body>
</html>