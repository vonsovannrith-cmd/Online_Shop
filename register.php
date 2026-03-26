<?php
include "db.php";

$error = "";
$success = "";

if(isset($_POST['submit'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = "user";

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result_check = $stmt->get_result();

    if($result_check->num_rows > 0){
        $error = "Email already registered!";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO user(name, email, password, phone, address, role) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $role);

        if($stmt->execute()){
            $success = "Registration successful! You can login now.";
        } else {
            $error = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<style>
*{margin:0; padding:0; box-sizing:border-box; font-family:Arial;}
body{height:100vh; display:flex; justify-content:center; align-items:center; background:linear-gradient(120deg,#43cea2,#185a9d);}
.register-box{background:white; padding:40px; width:340px; border-radius:10px; box-shadow:0 10px 25px rgba(0,0,0,0.2); text-align:center;}
.register-box h2{margin-bottom:20px;}
.register-box input, .register-box textarea{width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:5px;}
.register-box textarea{resize:none; height:70px;}
.register-box button{width:100%; padding:10px; background:#43cea2; border:none; color:white; font-size:16px; border-radius:5px; cursor:pointer;}
.register-box button:hover{background:#2bbf8f;}
.error{color:red; margin-bottom:10px;}
.success{color:green; margin-bottom:10px;}
.register-box a{color:#185a9d; text-decoration:none;}
.register-box a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="register-box">
<h2>Register</h2>

<?php if($error!=""){ ?><p class="error"><?php echo htmlspecialchars($error) ?></p><?php } ?>
<?php if($success!=""){ ?><p class="success"><?php echo htmlspecialchars($success) ?></p><?php } ?>

<form method="post">
<input type="text" name="name" placeholder="Enter your name" required>
<input type="email" name="email" placeholder="Enter your email" required>
<input type="password" name="password" placeholder="Enter password" required>
<input type="tel" name="phone" placeholder="Phone number" required>
<textarea name="address" placeholder="Enter your address"></textarea>
<button type="submit" name="submit">Sign Up</button>
</form>

<p style="margin-top:10px;">Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>