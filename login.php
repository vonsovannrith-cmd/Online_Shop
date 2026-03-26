<?php
include "db.php";
session_start();

$error = "";

if(isset($_POST['submit'])){

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare SQL to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, password, role FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();

        if(password_verify($password, $row['password'])){
            // Password correct, create session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_role'] = $row['role'];

            if($row['role'] == 'admin'){
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            $error = "Wrong password!";
        }
    } else {
        $error = "Account not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
*{margin:0; padding:0; box-sizing:border-box; font-family:Arial;}
body{height:100vh; display:flex; justify-content:center; align-items:center; background:linear-gradient(120deg,#4facfe,#00f2fe);}
.login-box{background:white; padding:40px; width:320px; border-radius:10px; box-shadow:0 10px 25px rgba(0,0,0,0.2); text-align:center;}
.login-box h2{margin-bottom:20px;}
.login-box input{width:100%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:5px;}
.login-box button{width:100%; padding:10px; background:#4facfe; color:white; border:none; border-radius:5px; cursor:pointer;}
.login-box button:hover{background:#2c7be5;}
.error{color:red; margin-bottom:10px;}
.show{display:flex; align-items:center; font-size:14px;}
</style>
</head>
<body>
<div class="login-box">
<h2>Login</h2>

<?php if($error!=""){ ?><p class="error"><?php echo htmlspecialchars($error) ?></p><?php } ?>

<form method="post">
<input type="email" name="email" placeholder="Email" required>
<input type="password" id="password" name="password" placeholder="Password" required>
<div class="show">
<input type="checkbox" onclick="showPassword()"> Show Password
</div>
<button type="submit" name="submit">Login</button>
</form>

<p style="margin-top:10px;">Don't have an account? <a href="register.php">Sign up</a></p>
</div>

<script>
function showPassword(){
    var x = document.getElementById("password");
    if(x.type === "password"){ x.type = "text"; }
    else{ x.type = "password"; }
}
</script>

</body>
</html>