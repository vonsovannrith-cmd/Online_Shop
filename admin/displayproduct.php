<?php
session_start();
include "../db.php";

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

// Check admin role
if($_SESSION['user_role'] != 'admin'){
    header("Location: admin/dashboard.php");
    exit();
}

// Get products
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
if(!$result){
    die("Database Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Products</title>
<style>
body{
    font-family: Arial, sans-serif;
    background:#f4f4f4;
    padding:20px;
}
h2{
    color:#00796b;
    margin-bottom:20px;
}
table{
    width:100%;
    border-collapse: collapse;
    background:white;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
th, td{
    padding:10px;
    text-align:center;
    border-bottom:1px solid #ddd;
}
th{
    background:#00796b;
    color:white;
}
tr:hover{
    background:#e0f2f1;
}
img{
    max-width:100px;
    height:auto;
    border-radius:5px;
}
a.update, a.delete{
    display:inline-block;
    padding:8px 12px;
    border-radius:5px;
    color:white;
    text-decoration:none;
}
a.update{
    background:#f44336; /* red */
}
a.update:hover{
    background:#d32f2f;
}
a.delete{
    background:#4caf50; /* green */
}
a.delete:hover{
    background:#388e3c;
}
@media(max-width:768px){
    table, thead, tbody, th, td, tr{
        display:block;
    }
    tr{
        margin-bottom:15px;
    }
    td{
        text-align:right;
        padding-left:50%;
        position:relative;
    }
    td::before{
        content: attr(data-label);
        position:absolute;
        left:10px;
        width:45%;
        text-align:left;
        font-weight:bold;
    }
}
</style>
<script>
function confirmDelete(){
    return confirm('Are you sure you want to delete this product?');
}
</script>
</head>
<body>
<h2>All Products</h2>

<table>
    <thead>
        <tr>
            <th>Product Title</th>
            <th>Description</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image</th>
            <th>Category</th>
            <th>Update</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td data-label="Product Title"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td data-label="Description"><?php echo htmlspecialchars($row['description']); ?></td>
                    <td data-label="Price"><?php echo number_format($row['price'], 2); ?></td>
                    <td data-label="Stock"><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td data-label="Image"><img src="../image/<?php echo htmlspecialchars($row['image']); ?>" alt=""></td>
                    <td data-label="Category"><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td data-label="Update"><a class="update" href="updateproduct.php?product_id=<?php echo $row['id']; ?>">Update</a></td>
                    <td data-label="Delete"><a class="delete" href="deleteproduct.php?product_id=<?php echo $row['id']; ?>" onclick="return confirmDelete();">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No products found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>