<?php
session_start();
include "../db.php";

// Admin check
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin'){
    header("Location: ../index.php");
    exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'add';
$message = "";

// Fetch categories (used in add & view pages)
$categories = mysqli_query($conn, "SELECT * FROM categories");

// ------------------ HANDLE ADD PRODUCT ------------------
if($page=='add' && isset($_POST['add_product'])){
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_name = trim($_POST['category_name']);

    if(isset($_FILES['image']) && $_FILES['image']['error']==0){
        $image = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        $upload_dir = "../image/";

        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if(!in_array($ext,$allowed)){
            $message = "Invalid image type! Only JPG, PNG, GIF allowed.";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category_name) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $image, $category_name);

            if($stmt->execute()){
                move_uploaded_file($tmp, $upload_dir.$image);
                $message = "Product added successfully!";
            } else {
                $message = "Error adding product: ".$stmt->error;
            }
            $stmt->close();
        }
    } else {
        $message = "Please upload an image.";
    }
}

// ------------------ HANDLE DELETE PRODUCT ------------------
if($page=='view' && isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    // First delete image file
    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        if(file_exists("../image/".$row['image'])){
            unlink("../image/".$row['image']);
        }
    }
    $stmt->close();

    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php?page=view");
    exit();
}

// ------------------ HANDLE INLINE UPDATE ------------------
if($page=='view' && isset($_POST['update_product'])){
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_name = trim($_POST['category_name']);

    // Keep old image by default
    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row_img = $res->fetch_assoc();
    $image = $row_img['image'];
    $stmt->close();

    // If new image uploaded
    if(isset($_FILES['image']) && $_FILES['image']['error']==0){
        $new_image = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($new_image, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if(in_array($ext,$allowed)){
            if(file_exists("../image/".$image)){
                unlink("../image/".$image);
            }
            move_uploaded_file($tmp,"../image/".$new_image);
            $image = $new_image;
        } else {
            $message = "Invalid image type! Only JPG, PNG, GIF allowed.";
        }
    }

    // Update product
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=?, category_name=? WHERE id=?");
    $stmt->bind_param("ssdissi", $name, $description, $price, $stock, $image, $category_name, $id);

    if($stmt->execute()){
        $message = "Product updated successfully!";
    } else {
        $message = "Error updating product: ".$stmt->error;
    }
    $stmt->close();
}

// ------------------ FETCH PRODUCTS ------------------
if($page=='view'){
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
body{display:flex;background:#f5f5f5;}
.dashboard_sidebar{
    width:220px;background:#00796b;color:white;height:100vh;position:fixed;padding-top:20px;
}
.dashboard_sidebar ul{list-style:none;}
.dashboard_sidebar ul li{margin-bottom:10px;}
.dashboard_sidebar ul li a{
    display:block;color:white;text-decoration:none;padding:10px 20px;border-radius:5px;
}
.dashboard_sidebar ul li a:hover{background:#004d40;}
.dashboard_main{margin-left:240px;padding:20px;width:100%;}
form input, form select, form textarea{width:90%;padding:5px;margin-bottom:5px;border:1px solid #ccc;border-radius:5px;}
form textarea{height:50px;}
form input[type="submit"]{background:#00796b;color:white;border:none;padding:5px 10px;cursor:pointer;border-radius:5px;}
form input[type="submit"]:hover{background:#004d40;}
.message{margin-bottom:15px;padding:10px;border-radius:5px;}
.success{background:#c8e6c9;color:#2e7d32;}
.error{background:#ffcdd2;color:#c62828;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
table, th, td{border:1px solid #ccc;}
th, td{padding:5px;text-align:center;}
img{width:60px;height:50px;object-fit:cover;}
.update-btn{background:orange;color:white;padding:3px 8px;text-decoration:none;border-radius:5px;}
.delete-btn{background:red;color:white;padding:3px 8px;text-decoration:none;border-radius:5px;}
</style>
</head>
<body>

<div class="dashboard_sidebar">
    <ul>
        <li><a href="dashboard.php?page=add">Add Product</a></li>
        <li><a href="dashboard.php?page=view">View Products</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard_main">

<?php if($page=='add'){ ?>
<h2>Add New Product</h2>
<?php if($message!=""){ ?>
    <div class="message <?php echo strpos($message,'success')!==false?'success':'error'; ?>">
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
        mysqli_data_seek($categories,0);
        while($row=mysqli_fetch_assoc($categories)){ ?>
            <option value="<?php echo htmlspecialchars($row['name']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
        <?php } ?>
    </select>
    <input type="submit" name="add_product" value="Add Product">
</form>

<?php } elseif($page=='view'){ ?>
<h2>All Products</h2>
<?php if($message!=""){ ?>
    <div class="message <?php echo strpos($message,'success')!==false?'success':'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php } ?>
<table>
<tr>
<th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Image</th><th>Category</th><th>Update</th><th>Delete</th>
</tr>
<?php while($row=mysqli_fetch_assoc($products)){ ?>
<tr>
<form action="dashboard.php?page=view" method="post" enctype="multipart/form-data">
    <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
    <td><textarea name="description"><?php echo htmlspecialchars($row['description']); ?></textarea></td>
    <td><input type="number" name="price" value="<?php echo $row['price']; ?>" step="0.01"></td>
    <td><input type="number" name="stock" value="<?php echo $row['stock']; ?>"></td>
    <td>
        <img src="../image/<?php echo $row['image']; ?>">
        <input type="file" name="image">
    </td>
    <td>
        <select name="category_name">
        <?php
        mysqli_data_seek($categories,0);
        while($cat=mysqli_fetch_assoc($categories)){ ?>
            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php if($cat['name']==$row['category_name']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php } ?>
        </select>
    </td>
    <td>
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="submit" name="update_product" value="Save">
    </td>
    <td><a class="delete-btn" href="dashboard.php?page=view&delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this product?')">Delete</a></td>
</form>
</tr>
<?php } ?>
</table>
<?php } ?>

</div>
</body>
</html>