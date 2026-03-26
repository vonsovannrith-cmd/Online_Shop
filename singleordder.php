<?php 
session_start();
include "db.php";

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// Check product and quantity
if(isset($_GET['product_id'], $_GET['product_price'], $_GET['qty'])){

    $user_id = intval($_SESSION['user_id']);
    $product_id = intval($_GET['product_id']);
    $total_amount = floatval($_GET['product_price']);
    $quantity = intval($_GET['qty']);

    // Check product stock
    $check_stock = "SELECT stock FROM products WHERE id='$product_id'";
    $result_stock = mysqli_query($conn, $check_stock);
    $row = mysqli_fetch_assoc($result_stock);

    if($row['stock'] < $quantity){
        echo "Not enough stock! Available: " . $row['stock'];
        exit();
    }

    // Insert order
    $sql = "INSERT INTO single_order(user_id, product_id, total_amount) 
            VALUES ('$user_id', '$product_id', '$total_amount')";
    $result = mysqli_query($conn, $sql);
    if(!$result){
        echo "Error inserting order: " . mysqli_error($conn);
        exit();
    }

    // Get order id
    $order_id = mysqli_insert_id($conn);

    // Insert payment with status
    $payment_method = "cashon";
    $status = "pending"; // default payment status

    $sql_payment = "INSERT INTO payment(order_id, user_id, total_amount, payment_method, status)
                    VALUES ('$order_id', '$user_id', '$total_amount', '$payment_method', '$status')";
    $result_payment = mysqli_query($conn, $sql_payment);
    if(!$result_payment){
        echo "Error inserting payment: " . mysqli_error($conn);
        exit();
    }

    // Update stock
    $update_stock = "UPDATE products SET stock = stock - $quantity WHERE id='$product_id'";
    $result_update_stock = mysqli_query($conn, $update_stock);
    if(!$result_update_stock){
        echo "Error updating stock: " . mysqli_error($conn);
        exit();
    }

    echo "Order added successfully! <a href='index.php'>Buy More</a>";

}else{
    header("Location: index.php");
    exit();
}
?>