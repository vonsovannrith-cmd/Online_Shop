<?php
session_start();
include "db.php";

// Check if user is logged in and role is user
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'user'){
    if(isset($_SESSION['user_role']) && $_SESSION['user_role']=='admin'){
        header("Location: admin/dashboard.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

// Get products and categories
$sql_product = "SELECT * FROM products WHERE stock > 0 ORDER BY id DESC";
$result_product = mysqli_query($conn,$sql_product);

$sql_category = "SELECT * FROM categories";
$result_category = mysqli_query($conn,$sql_category);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard - Online Shop</title>
<style>
body{font-family:Arial;background:#f5f5f5;margin:0;padding:0;}
.dashboard_sidebar{
    width:200px;position:fixed;top:0;left:0;background:#00796b;color:white;height:100vh;padding-top:30px;
}
.dashboard_sidebar ul{list-style:none;padding:0;}
.dashboard_sidebar ul li{margin-bottom:10px;}
.dashboard_sidebar ul li a{display:block;color:white;text-decoration:none;padding:10px 15px;border-radius:5px;}
.dashboard_sidebar ul li a:hover{background:#004d40;}
.dashboard_main{margin-left:220px;padding:20px;}
.header{
    background:#232f3e;color:white;display:flex;justify-content:space-between;align-items:center;padding:15px 30px;border-radius:5px;
    margin-bottom:20px;
}
.header .logo{font-size:22px;font-weight:bold;}
.header .menu a{color:white;text-decoration:none;margin-left:20px;}
.header .menu a.cart{position:relative;}
.header .menu a.cart::after{
    content:"<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>";
    position:absolute; top:-8px; right:-10px; background:red; color:white; font-size:12px; padding:2px 6px; border-radius:50%;
}
.categories{margin:20px 0;}
.categories button{padding:10px 20px;margin:5px;border:none;background:#232f3e;color:white;cursor:pointer;border-radius:5px;}
.categories button:hover{background:#ff9900;}
.products{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:20px;padding:20px;}
.product{background:white;padding:15px;border-radius:10px;box-shadow:0 3px 10px rgba(0,0,0,0.1);text-align:center;transition:0.3s;}
.product:hover{transform:translateY(-5px);}
.product img{width:100%;height:160px;object-fit:contain;border-radius:5px;}
.product h3{margin:10px 0;}
.price{color:red;font-weight:bold;margin:5px 0;}
.buy{display:inline-block;padding:8px 15px;background:#28a745;color:white;text-decoration:none;border-radius:5px;margin-top:10px;}
.buy:hover{background:#218838;}
.search-bar{text-align:center;margin-bottom:20px;}
.search-bar input{padding:8px 12px;width:300px;border-radius:5px;border:1px solid #ccc;}
.footer{background:#232f3e;color:white;text-align:center;padding:15px;margin-top:30px;border-radius:5px;}
</style>
</head>
<body>

<div class="dashboard_sidebar">
    <ul>
        <li><a href="dashboard_user.php">Shop</a></li>
        <li><a href="myorders.php">My Orders</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="dashboard_main">

    <div class="header">
        <div class="logo">🛒 Online Shop</div>
        <div class="menu">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="cart.php" class="cart">Cart</a>
        </div>
    </div>

    <!-- SEARCH -->
    <div class="search-bar">
        <input type="text" placeholder="Search products..." id="searchInput" onkeyup="searchProducts()">
    </div>

    <!-- CATEGORY BUTTONS -->
    <div class="categories">
        <button onclick="filterProduct('all')">All</button>
        <?php while($row_category = mysqli_fetch_assoc($result_category)){ ?>
            <button onclick="filterProduct('<?php echo $row_category['name']; ?>')">
                <?php echo $row_category['name']; ?>
            </button>
        <?php } ?>
    </div>

    <!-- PRODUCTS GRID -->
    <div class="products" id="productGrid">
        <?php
        mysqli_data_seek($result_product,0); // reset pointer
        while($row_product = mysqli_fetch_assoc($result_product)){ ?>
        <div class="product" data-category="<?php echo $row_product['category_name']; ?>">
            <a href="product_detail.php?id=<?php echo $row_product['id']; ?>">
                <img src="image/<?php echo $row_product['image']; ?>" alt="">
                <h3><?php echo $row_product['name']; ?></h3>
            </a>
            <p class="price">$<?php echo $row_product['price']; ?></p>
            <p>Stock: <?php echo $row_product['stock']; ?></p>
            <?php if(isset($_SESSION['user_id'])){ ?>
                <a class="buy" href="singleordder.php?product_id=<?php echo $row_product['id']; ?>&product_price=<?php echo $row_product['price']; ?>&qty=1">Buy</a>
            <?php } else { ?>
                <a class="buy" href="login.php">Buy</a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

</div>

<div class="footer">Copyright © Sovannrith</div>

<script>
// Category Filter
function filterProduct(category){
    let products = document.querySelectorAll(".product");
    products.forEach(p=>{
        if(category=='all' || p.dataset.category==category){
            p.style.display="block";
        } else {
            p.style.display="none";
        }
    });
}

// Search Filter
function searchProducts(){
    let input = document.getElementById('searchInput').value.toLowerCase();
    let products = document.querySelectorAll(".product");
    products.forEach(p=>{
        let name = p.querySelector('h3').innerText.toLowerCase();
        if(name.includes(input)){
            p.style.display="block";
        } else {
            p.style.display="none";
        }
    });
}
</script>

</body>
</html>