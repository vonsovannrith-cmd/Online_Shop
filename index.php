<?php
session_start();
include "db.php";

// Get products
$sql_product = "SELECT * FROM products WHERE stock > 0";
$result_product = mysqli_query($conn,$sql_product);

// Get categories
$sql_category = "SELECT * FROM categories";
$result_category = mysqli_query($conn,$sql_category);

// Cart count
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>Online Shop</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    transition:0.3s;
}

/* DARK MODE BODY */
body.dark{
    background:#111827;
    color:white;
}

/* HEADER */
.header{
    background:#1f2937;
    color:white;
    display:flex;
    justify-content:space-between;
    padding:15px 30px;
    align-items:center;
}

.logo{
    font-size:22px;
    font-weight:bold;
}

/* MENU */
.menu a{
    color:white;
    margin-left:15px;
    text-decoration:none;
}

/* DARK MODE BUTTON */
.dark-btn{
    margin-left:15px;
    padding:6px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    background:#facc15;
    font-weight:bold;
}

/* DARK BUTTON STYLE */
body.dark .dark-btn{
    background:#2563eb;
    color:white;
}

/* CART */
.cart{
    position:relative;
}

.cart span{
    position:absolute;
    top:-8px;
    right:-10px;
    background:red;
    color:white;
    font-size:11px;
    padding:2px 6px;
    border-radius:50%;
}

/* SEARCH */
.search-bar{
    text-align:center;
    margin:20px;
}

.search-bar input{
    width:300px;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

/* DARK SEARCH */
body.dark .search-bar input{
    background:#1f2937;
    color:white;
    border:1px solid #374151;
}

/* CATEGORY */
.categories{
    text-align:center;
}

.categories button{
    padding:10px 18px;
    margin:6px;
    border:none;
    border-radius:20px;
    background:#e5e7eb;
    cursor:pointer;
}

.categories button.active,
.categories button:hover{
    background:#2563eb;
    color:white;
}

/* DARK CATEGORY */
body.dark .categories button{
    background:#374151;
    color:white;
}

body.dark .categories button.active,
body.dark .categories button:hover{
    background:#2563eb;
}

/* PRODUCTS */
.products{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(230px,1fr));
    gap:20px;
    padding:20px;
}

.product{
    background:white;
    padding:15px;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
    text-align:center;
    transition:0.3s;
}

.product:hover{
    transform:translateY(-6px);
}

/* DARK PRODUCT */
body.dark .product{
    background:#1f2937;
    color:white;
    box-shadow:0 6px 18px rgba(0,0,0,0.4);
}

.product img{
    width:100%;
    height:160px;
    object-fit:contain;
    transition:0.3s;
}

.product:hover img{
    transform:scale(1.05);
}

/* TITLE */
.product-name{
    font-size:15px;
    margin:10px 0;
    color:#111;

    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}

/* DARK TITLE */
body.dark .product-name{
    color:white;
}

/* PRICE */
.price{
    color:#dc2626;
    font-size:18px;
    font-weight:bold;
}

/* STOCK */
.stock{
    color:#6b7280;
    font-size:13px;
}

/* DARK STOCK */
body.dark .stock{
    color:#d1d5db;
}

/* BUTTON */
.buy{
    display:block;
    margin-top:10px;
    padding:10px;
    border-radius:8px;
    background:#2563eb;
    color:white;
    text-decoration:none;
}

.buy:hover{
    background:#1e40af;
}

.buy.login{
    background: green;
}

/* NO PRODUCT */
#noProduct{
    text-align:center;
    color:#999;
    display:none;
}

/* FOOTER */
.footer{
    text-align:center;
    padding:20px;
    background:#1f2937;
    color:white;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="logo">🛒 Online Shop</div>

    <div class="menu">
        <?php if(!isset($_SESSION['user_id'])){ ?>
            <a href="login.php">Login</a>
            <a href="register.php">Signup</a>
        <?php } else { ?>
            <a href="admin/dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php } ?>

        <a href="cart.php" class="cart">
            🛒 <span><?= $cartCount ?></span>
        </a>

        <!-- DARK MODE BUTTON -->
        <button class="dark-btn" onclick="toggleDarkMode()" id="darkBtn">🌙 Dark</button>
    </div>
</div>

<!-- SEARCH -->
<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search products..." onkeyup="filterAll()">
</div>

<!-- CATEGORY -->
<div class="categories">
    <button class="active" onclick="setCategory('all', this)">All</button>

    <?php while($cat = mysqli_fetch_assoc($result_category)){ ?>
        <button onclick="setCategory('<?= htmlspecialchars($cat['name']) ?>', this)">
            <?= htmlspecialchars($cat['name']) ?>
        </button>
    <?php } ?>
</div>

<!-- PRODUCTS -->
<div class="products">

<?php while($p = mysqli_fetch_assoc($result_product)){ ?>
<div class="product"
     data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
     data-category="<?= htmlspecialchars($p['category_name']) ?>">

    <a href="product_detail.php?id=<?= $p['id'] ?>" style="text-decoration:none;">
        <img src="image/<?= htmlspecialchars($p['image']) ?>">
        <div class="product-name">
            <?= htmlspecialchars($p['name']) ?>
        </div>
    </a>

    <div class="price">$<?= number_format($p['price'],2) ?></div>
    <div class="stock">Stock: <?= $p['stock'] ?></div>

    <?php if(isset($_SESSION['user_id'])){ ?>
        <a class="buy" href="singleordder.php?product_id=<?= $p['id'] ?>">
            Buy Now
        </a>
    <?php } else { ?>
        <a class="buy login" href="login.php">
            Login to Buy
        </a>
    <?php } ?>

</div>
<?php } ?>

</div>

<div id="noProduct">❌ No products found</div>

<div class="footer">© Sovannrith</div>

<!-- JS -->
<script>
let currentCategory = "all";

function setCategory(category, el){
    currentCategory = category;

    document.querySelectorAll(".categories button").forEach(btn=>{
        btn.classList.remove("active");
    });

    el.classList.add("active");
    filterAll();
}

function filterAll(){
    let search = document.getElementById("searchInput").value.toLowerCase();
    let found = false;

    document.querySelectorAll(".product").forEach(p=>{
        let name = p.dataset.name;
        let category = p.dataset.category;

        let matchSearch = name.includes(search);
        let matchCategory = currentCategory === "all" || category === currentCategory;

        if(matchSearch && matchCategory){
            p.style.display = "block";
            found = true;
        } else {
            p.style.display = "none";
        }
    });

    document.getElementById("noProduct").style.display = found ? "none" : "block";
}

/* DARK MODE FUNCTION */
function toggleDarkMode(){
    document.body.classList.toggle("dark");

    if(document.body.classList.contains("dark")){
        localStorage.setItem("darkMode", "enabled");
        document.getElementById("darkBtn").innerHTML = "☀️ Light";
    }else{
        localStorage.setItem("darkMode", "disabled");
        document.getElementById("darkBtn").innerHTML = "🌙 Dark";
    }
}

/* LOAD DARK MODE SETTING */
window.onload = function(){
    if(localStorage.getItem("darkMode") === "enabled"){
        document.body.classList.add("dark");
        document.getElementById("darkBtn").innerHTML = "☀️ Light";
    }
}
</script>

</body>
</html>