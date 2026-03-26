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
}
.info p.description{
    font-size:16px;
    line-height:1.6;
    margin-bottom:15px;
    color:#555;
}
.price{
    font-size:26px;
    font-weight:bold;
    color:#e53935;
    margin-bottom:15px;
}
.stock{
    font-size:15px;
    color:#777;
    margin-bottom:20px;
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
</script>

</body>
</html>