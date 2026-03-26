<?php
session_start();
include "db.php";

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Initialize cart if not exists
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

// Handle Remove item
if(isset($_GET['remove'])){
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

// Handle Add to Cart (optional if adding from product_detail.php)
if(isset($_GET['add'], $_GET['qty'])){
    $id = intval($_GET['add']);
    $qty = intval($_GET['qty']);
    if(isset($_SESSION['cart'][$id])){
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    header("Location: cart.php");
    exit();
}

// Fetch products in cart
$cart_items = [];
$total_amount = 0;
if(!empty($_SESSION['cart'])){
    $ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)){
        $row['quantity'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total_amount += $row['subtotal'];
        $cart_items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Shopping Cart</title>
<style>
body{font-family:Arial; background:#f5f5f5;}
.container{width:900px; margin:50px auto; background:white; padding:20px; border-radius:10px;}
table{width:100%; border-collapse: collapse;}
th, td{padding:10px; text-align:center; border-bottom:1px solid #ccc;}
th{background:#232f3e; color:white;}
a{color:#28a745; text-decoration:none;}
a:hover{color:#218838;}
.total{text-align:right; margin-top:10px; font-size:20px;}
.checkout{display:inline-block; padding:10px 20px; background:#28a745; color:white; text-decoration:none; border-radius:5px; margin-top:10px;}
.checkout:hover{background:#218838;}
</style>
</head>
<body>

<div class="container">
<h2>Your Cart</h2>

<?php if(empty($cart_items)){ ?>
<p>Your cart is empty. <a href="index.php">Go shopping</a></p>
<?php } else { ?>

<table>
<tr>
<th>Product</th>
<th>Price</th>
<th>Quantity</th>
<th>Subtotal</th>
<th>Action</th>
</tr>

<?php foreach($cart_items as $item){ ?>
<tr>
<td><?php echo $item['name']; ?></td>
<td>$<?php echo $item['price']; ?></td>
<td><?php echo $item['quantity']; ?></td>
<td>$<?php echo $item['subtotal']; ?></td>
<td><a href="cart.php?remove=<?php echo $item['id']; ?>">Remove</a></td>
</tr>
<?php } ?>
</table>

<p class="total"><b>Total:</b> $<?php echo $total_amount; ?></p>

<a class="checkout" href="checkout.php">Proceed to Checkout</a>

<?php } ?>
</div>

</body>
</html>