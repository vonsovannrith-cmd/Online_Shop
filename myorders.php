<?php
session_start();
include "db.php";

// Check if user is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'user'){
    if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'){
        header("Location: admin/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch payments for this user
$sql = "SELECT * FROM payment WHERE user_id = $user_id ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if(!$result){
    die("Database Error: ".$conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Payments</title>
<style>
body{
    font-family: Arial, sans-serif;
    background:#f5f5f5;
    padding:20px;
}

h2{
    text-align:center;
    color:#00796b;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse: collapse;
    background:white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

th, td{
    padding:12px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

th{
    background:#00796b;
    color:white;
}

tr:nth-child(even){
    background:#f2f2f2;
}

tr:hover{
    background:#c8e6c9;
}

.no-data{
    text-align:center;
    padding:20px;
    font-size:16px;
    color:#555;
}
</style>
</head>
<body>

<h2>My Payments</h2>

<?php if(mysqli_num_rows($result) > 0){ ?>
<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Total Amount</th>
            <th>Payment Method</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
            <td><?php echo number_format($row['total_amount'],2); ?> $</td>
            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php } else { ?>
<div class="no-data">
    You have no payments yet.
</div>
<?php } ?>

</body>
</html>