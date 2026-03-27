<?php
session_start();
include "db.php"; 

// 1️⃣ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// 2️⃣ Get GET params
if (!isset($_GET['product_id'], $_GET['qty'])) {
    die("❌ Missing product parameters!");
}

$product_id = intval($_GET['product_id']);
$quantity   = intval($_GET['qty']);

if ($quantity <= 0) $quantity = 1;

// 3️⃣ Fetch product info (price + stock)
$stmt = $conn->prepare("SELECT stock, name, price FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) die("❌ Product not found!");
if ($product['stock'] < $quantity) die("❌ Not enough stock! Available: " . $product['stock']);

// 4️⃣ Calculate total amount securely
$total_amount = $product['price'] * $quantity;

// 5️⃣ Insert into single_order
$stmt_order = $conn->prepare("
    INSERT INTO single_order (user_id, product_id, total_amount, quantity) 
    VALUES (?, ?, ?, ?)
");
$stmt_order->bind_param("iidi", $user_id, $product_id, $total_amount, $quantity);

if (!$stmt_order->execute()) {
    die("❌ Failed to insert order: " . $stmt_order->error);
}

$order_id = $stmt_order->insert_id;
$stmt_order->close();

// 6️⃣ Insert into payment
$payment_method = "cashon";
$status = "pending";

$stmt_payment = $conn->prepare("
    INSERT INTO payment (order_id, user_id, total_amount, payment_method, status) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt_payment->bind_param("iidss", $order_id, $user_id, $total_amount, $payment_method, $status);

if (!$stmt_payment->execute()) {
    die("❌ Failed to insert payment: " . $stmt_payment->error);
}
$stmt_payment->close();

// 7️⃣ Update stock
$stmt_stock = $conn->prepare("
    UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?
");
$stmt_stock->bind_param("iii", $quantity, $product_id, $quantity);

if (!$stmt_stock->execute()) {
    die("❌ Failed to update stock: " . $stmt_stock->error);
}
$stmt_stock->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Success</title>

<style>
body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f4f6f9;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    transition:0.3s;
}

body.dark{
    background:#111827;
    color:white;
}

.box{
    background:white;
    padding:30px;
    border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,0.15);
    text-align:center;
    width:90%;
    max-width:450px;
    transition:0.3s;
}

body.dark .box{
    background:#1f2937;
    box-shadow:0 6px 18px rgba(0,0,0,0.5);
}

.box h2{
    color:green;
}

body.dark .box h2{
    color:#22c55e;
}

.box a{
    display:inline-block;
    margin-top:15px;
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
    background:#2563eb;
    color:white;
    transition:0.3s;
}

.box a:hover{
    background:#1e40af;
}

.dark-btn{
    position:absolute;
    top:20px;
    right:20px;
    padding:8px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
    background:#facc15;
}

body.dark .dark-btn{
    background:#2563eb;
    color:white;
}
</style>
</head>

<body>

<button class="dark-btn" onclick="toggleDarkMode()" id="darkBtn">🌙 Dark</button>

<div class="box">
    <h2>✅ Order Success!</h2>

    <p>
        Your order for <b><?php echo htmlspecialchars($product['name']); ?></b>
        has been placed successfully.
    </p>

    <p>
        Quantity: <b><?php echo $quantity; ?></b><br>
        Total: <b>$<?php echo number_format($total_amount,2); ?></b>
    </p>

    <a href="index.php">🛒 Buy More</a>
    <a href="myorders.php">📦 View My Orders</a>
</div>

<script>
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

window.onload = function(){
    if(localStorage.getItem("darkMode") === "enabled"){
        document.body.classList.add("dark");
        document.getElementById("darkBtn").innerHTML = "☀️ Light";
    }
}
</script>

</body>
</html>