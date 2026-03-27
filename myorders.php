<?php
session_start();
include "db.php";

// 1️⃣ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2️⃣ Redirect based on role
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['user_role'] !== 'user') {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// 3️⃣ Fetch payments securely
$stmt = $conn->prepare("SELECT * FROM payment WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Database Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Payments</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 20px;
    transition:0.3s;
}

/* DARK MODE BODY */
body.dark{
    background:#111827;
    color:white;
}

h2 {
    text-align: center;
    color: #00796b;
    margin-bottom: 20px;
}

/* DARK MODE TITLE */
body.dark h2{
    color:#22c55e;
}

/* DARK MODE BUTTON */
.dark-btn{
    position:fixed;
    top:20px;
    right:20px;
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

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-radius: 10px;
    overflow: hidden;
    transition:0.3s;
}

/* DARK MODE TABLE */
body.dark table{
    background:#1f2937;
    box-shadow:0 5px 15px rgba(0,0,0,0.5);
}

th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

/* DARK MODE CELL BORDER */
body.dark th, 
body.dark td{
    border-bottom:1px solid #374151;
}

th {
    background: #00796b;
    color: white;
}

/* DARK MODE HEADER */
body.dark th{
    background:#0f766e;
}

tr:nth-child(even) {
    background: #f2f2f2;
}

/* DARK MODE EVEN ROW */
body.dark tr:nth-child(even){
    background:#111827;
}

tr:hover {
    background: #c8e6c9;
}

/* DARK MODE HOVER */
body.dark tr:hover{
    background:#334155;
}

.no-data {
    text-align: center;
    padding: 20px;
    font-size: 16px;
    color: #555;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* DARK MODE NO DATA */
body.dark .no-data{
    background:#1f2937;
    color:#d1d5db;
    box-shadow:0 5px 15px rgba(0,0,0,0.5);
}

.status-pending {
    color: #f57c00;
    font-weight: bold;
}
.status-completed {
    color: #388e3c;
    font-weight: bold;
}
.status-failed {
    color: #d32f2f;
    font-weight: bold;
}
</style>
</head>

<body>

<!-- DARK MODE BUTTON -->
<button class="dark-btn" onclick="toggleDarkMode()" id="darkBtn">🌙 Dark</button>

<h2>My Payments</h2>

<?php if ($result->num_rows > 0) { ?>
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
        <?php while ($row = $result->fetch_assoc()) { 
            $status_class = '';
            if ($row['status'] === 'pending') $status_class = 'status-pending';
            elseif ($row['status'] === 'completed') $status_class = 'status-completed';
            elseif ($row['status'] === 'failed') $status_class = 'status-failed';
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
            <td><?php echo number_format(floatval($row['total_amount']), 2); ?> $</td>
            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
            <td class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></td>
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

<script>
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

// Load saved mode
window.onload = function(){
    if(localStorage.getItem("darkMode") === "enabled"){
        document.body.classList.add("dark");
        document.getElementById("darkBtn").innerHTML = "☀️ Light";
    }
}
</script>

</body>
</html>