<?php 
include "db.php";

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id='$id'";
    $result = mysqli_query($conn,$sql);
    $row = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?php echo htmlspecialchars($row['name']); ?> - Online Shop</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
/* Reset & body */
body{
    font-family: Arial, sans-serif;
    background:#f9f9f9;
    margin:0;
    padding:0;
    transition:0.3s;
}

/* DARK MODE BODY */
body.dark{
    background:#111827;
    color:white;
}

/* Container */
.product-detail{
    max-width:900px;
    margin:50px auto;
    background:white;
    padding:30px;
    border-radius:12px;
    display:flex;
    flex-wrap:wrap;
    gap:40px;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
    transition:0.3s;
}

/* DARK PRODUCT DETAIL */
body.dark .product-detail{
    background:#1f2937;
    box-shadow:0 6px 20px rgba(0,0,0,0.4);
}

/* Image */
.image-box{
    flex:1 1 350px;
    overflow:hidden;
    border-radius:10px;
    text-align:center;
}
.image-box img{
    width:100%;
    max-height:350px;
    object-fit:contain;
    transition:0.4s;
    cursor:zoom-in;
    border-radius:10px;
}
.image-box img:hover{
    transform:scale(1.1);
}

/* Product Info */
.info{
    flex:1 1 400px;
    display:flex;
    flex-direction:column;
}
.info h2{
    margin-bottom:15px;
    font-size:28px;
    color:#333;
    transition:0.3s;
}
body.dark .info h2{
    color:white;
}
.info p.description{
    font-size:16px;
    line-height:1.6;
    margin-bottom:15px;
    color:#555;
    transition:0.3s;
}
body.dark .info p.description{
    color:#d1d5db;
}
.price{
    font-size:26px;
    font-weight:bold;
    color:#e53935;
    margin-bottom:15px;
}
body.dark .price{
    color:#f87171;
}
.stock{
    font-size:15px;
    color:#777;
    margin-bottom:20px;
}
body.dark .stock{
    color:#d1d5db;
}

/* Quantity & Buy */
.quantity{
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
}
.quantity label{
    font-weight:bold;
}
.quantity input{
    width:60px;
    padding:5px;
    text-align:center;
    border:1px solid #ccc;
    border-radius:5px;
    transition:0.3s;
}
body.dark .quantity input{
    background:#374151;
    color:white;
    border:1px solid #4b5563;
}

.buy{
    display:inline-block;
    padding:12px 25px;
    background:#28a745;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-weight:bold;
    transition:0.3s;
}
.buy:hover{
    background:#218838;
}
body.dark .buy{
    background:#2563eb;
}
body.dark .buy:hover{
    background:#1e40af;
}

/* Dark mode toggle button */
.dark-btn{
    position:fixed;
    top:15px;
    right:15px;
    padding:8px 15px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    background:#facc15;
    font-weight:bold;
    z-index:999;
}
body.dark .dark-btn{
    background:#2563eb;
    color:white;
}

/* Responsive */
@media (max-width:768px){
    .product-detail{
        flex-direction:column;
        padding:20px;
    }
    .image-box, .info{
        flex:1 1 100%;
    }
    .info h2{
        font-size:24px;
    }
}
</style>
</head>
<body>

<!-- Dark Mode Toggle -->
<button class="dark-btn" id="darkBtn">🌙 Dark</button>

<div class="product-detail">

    <div class="image-box">
        <img src="image/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
    </div>

    <div class="info">
        <h2><?php echo htmlspecialchars($row['name']); ?></h2>
        <p class="description"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
        <p class="price">$<?php echo number_format($row['price'],2); ?></p>
        <p class="stock"><b>Stock:</b> <?php echo intval($row['stock']); ?></p>

        <div class="quantity">
            <label>Quantity:</label>
            <input type="number" value="1" min="1" max="<?php echo intval($row['stock']); ?>" id="qty">
        </div>

        <a class="buy" href="singleordder.php?product_id=<?php echo $row['id']; ?>&product_price=<?php echo $row['price']; ?>&qty=1">
            Buy Now
        </a>
    </div>

</div>

<script>
// Update Buy link when quantity changes
let qtyInput = document.getElementById('qty');
let buyBtn = document.querySelector('.buy');

qtyInput.addEventListener('input', ()=>{
    let qty = parseInt(qtyInput.value);
    if(isNaN(qty) || qty < 1) qty = 1;
    if(qty > <?php echo intval($row['stock']); ?>) qty = <?php echo intval($row['stock']); ?>;
    qtyInput.value = qty;
    let price = <?php echo $row['price']; ?>;
    buyBtn.href = `singleordder.php?product_id=<?php echo $row['id']; ?>&product_price=${(price*qty).toFixed(2)}&qty=${qty}`;
});

// Dark Mode
const darkBtn = document.getElementById('darkBtn');

function toggleDarkMode(){
    document.body.classList.toggle('dark');
    if(document.body.classList.contains('dark')){
        localStorage.setItem('darkMode','enabled');
        darkBtn.innerHTML = '☀️ Light';
    }else{
        localStorage.setItem('darkMode','disabled');
        darkBtn.innerHTML = '🌙 Dark';
    }
}

// Load saved dark mode
window.onload = function(){
    if(localStorage.getItem('darkMode') === 'enabled'){
        document.body.classList.add('dark');
        darkBtn.innerHTML = '☀️ Light';
    }
}
</script>

</body>
</html>