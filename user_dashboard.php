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
body{
    font-family:Arial, sans-serif;
    background:#f5f5f5;
    margin:0;
    padding:0;
    transition:0.3s;
}

/* DARK MODE */
body.dark{
    background:#111827;
    color:white;
}

/* Sidebar */
.dashboard_sidebar{
    width:200px;
    position:fixed;
    top:0;
    left:0;
    background:#00796b;
    color:white;
    height:100vh;
    padding-top:30px;
    transition:0.3s;
}

body.dark .dashboard_sidebar{
    background:#0f172a;
}

.dashboard_sidebar ul{list-style:none;padding:0;}
.dashboard_sidebar ul li{margin-bottom:10px;}
.dashboard_sidebar ul li a{
    display:block;
    color:white;
    text-decoration:none;
    padding:10px 15px;
    border-radius:5px;
}
.dashboard_sidebar ul li a:hover{background:#004d40;}

body.dark .dashboard_sidebar ul li a:hover{
    background:#1e293b;
}

/* Main content */
.dashboard_main{margin-left:220px;padding:20px;}

/* Header */
.header{
    background:#232f3e;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 30px;
    border-radius:5px;
    margin-bottom:20px;
    transition:0.3s;
}

body.dark .header{
    background:#1f2937;
}

.header .logo{font-size:22px;font-weight:bold;}

.header .menu{
    display:flex;
    align-items:center;
    gap:15px;
}

.header .menu a{
    color:white;
    text-decoration:none;
    margin-left:10px;
    position:relative;
}

.header .menu a.cart::after{
    content:"<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>";
    position:absolute;
    top:-8px;
    right:-10px;
    background:red;
    color:white;
    font-size:12px;
    padding:2px 6px;
    border-radius:50%;
}

/* Dark mode button */
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

/* Search bar */
.search-bar{text-align:center;margin-bottom:20px;}
.search-bar input{
    padding:10px 12px;
    width:300px;
    border-radius:8px;
    border:1px solid #ccc;
    transition:0.3s;
}

body.dark .search-bar input{
    background:#1f2937;
    color:white;
    border:1px solid #374151;
}

/* Categories */
.categories{margin:20px 0;text-align:center;}
.categories button{
    padding:10px 18px;
    margin:5px;
    border:none;
    border-radius:20px;
    background:#232f3e;
    color:white;
    cursor:pointer;
    transition:0.3s;
}

.categories button:hover{background:#ff9900;color:black;}

body.dark .categories button{
    background:#374151;
}

body.dark .categories button:hover{
    background:#2563eb;
    color:white;
}

/* Products grid */
.products{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:20px;
    padding:0 10px;
}

/* Product card */
.product{
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
    text-align:center;
    transition:0.3s;
}

.product:hover{transform:translateY(-5px);}

body.dark .product{
    background:#1f2937;
    color:white;
    box-shadow:0 3px 10px rgba(0,0,0,0.5);
}

.product img{
    width:100%;
    height:160px;
    object-fit:contain;
    border-radius:5px;
    transition:0.3s;
}

.product:hover img{transform:scale(1.05);}

/* Title */
.product h3{
    margin:10px 0 5px 0;
    font-size:16px;
    color:#111;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
    text-overflow:ellipsis;
    min-height:42px;
}

body.dark .product h3{
    color:white;
}

/* Price */
.price{
    color:#e53935;
    font-weight:bold;
    font-size:18px;
    margin:5px 0;
}

/* Stock */
.stock{
    color:#757575;
    font-size:13px;
    margin-bottom:10px;
}

body.dark .stock{
    color:#cbd5e1;
}

/* Buy button */
.buy{
    display:inline-block;
    padding:8px 15px;
    background:#28a745;
    color:white;
    text-decoration:none;
    border-radius:5px;
    transition:0.3s;
}
.buy:hover{background:#218838;}

/* Footer */
.footer{
    background:#232f3e;
    color:white;
    text-align:center;
    padding:15px;
    margin-top:30px;
    border-radius:5px;
    transition:0.3s;
}

body.dark .footer{
    background:#1f2937;
}
</style>
</head>

<body>

<div class="dashboard_sidebar">
    <ul>
        <li><a href="user_dashboard.php">Shop</a></li>
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

            <!-- DARK MODE BUTTON -->
            <button class="dark-btn" onclick="toggleDarkMode()" id="darkBtn">🌙 Dark</button>
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
            <button onclick="filterProduct('<?php echo htmlspecialchars($row_category['name']); ?>')">
                <?php echo htmlspecialchars($row_category['name']); ?>
            </button>
        <?php } ?>
    </div>

    <!-- PRODUCTS GRID -->
    <div class="products" id="productGrid">
        <?php while($row_product = mysqli_fetch_assoc($result_product)){ ?>
        <div class="product" data-category="<?php echo htmlspecialchars($row_product['category_name']); ?>">
            <a href="product_detail.php?id=<?php echo $row_product['id']; ?>" style="text-decoration:none;">
                <img src="image/<?php echo htmlspecialchars($row_product['image']); ?>" alt="">
                <h3><?php echo htmlspecialchars($row_product['name']); ?></h3>
            </a>

            <p class="price">$<?php echo number_format($row_product['price'],2); ?></p>
            <p class="stock">Stock: <?php echo $row_product['stock']; ?></p>

            <!-- ✅ SECURE BUY LINK (NO PRICE IN URL) -->
            <a class="buy" href="singleordder.php?product_id=<?php echo $row_product['id']; ?>&qty=1">
                Buy
            </a>
        </div>
        <?php } ?>
    </div>

</div>

<div class="footer">© Sovannrith</div>

<script>
// Category Filter
function filterProduct(category){
    let products = document.querySelectorAll(".product");
    products.forEach(p=>{
        p.style.display = (category=='all' || p.dataset.category==category) ? "block" : "none";
    });
}

// Search Filter
function searchProducts(){
    let input = document.getElementById('searchInput').value.toLowerCase();
    let products = document.querySelectorAll(".product");
    products.forEach(p=>{
        let name = p.querySelector('h3').innerText.toLowerCase();
        p.style.display = name.includes(input) ? "block" : "none";
    });
}

/* DARK MODE FUNCTION */
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

/* LOAD SAVED MODE */
window.onload = function(){
    if(localStorage.getItem("darkMode") === "enabled"){
        document.body.classList.add("dark");
        document.getElementById("darkBtn").innerHTML = "☀️ Light";
    }
}
</script>

</body>
</html>