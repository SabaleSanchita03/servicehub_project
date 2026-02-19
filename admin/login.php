<?php
session_start();
require '../config/db.php';

// If already logged in → go to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = md5($_POST['password']); // matches inserted admin password

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $admin = $stmt->fetch();

    if ($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | ServiceHub</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: linear-gradient(135deg, #6366f1, #4338ca);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-card {
    background: #ffffff;
    padding: 40px;
    width: 360px;
    border-radius: 18px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.servicehub-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: center;
    margin-bottom: 25px;
    text-decoration: none;
}

.logo-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #4338ca);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.2rem;
}

.logo-text {
    font-weight: 800;
    font-size: 1.5rem;
    color: #1e293b;
}

.logo-text span {
    color: #6366f1;
}

h3 {
    text-align: center;
    margin-bottom: 20px;
    color: #475569;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: #6366f1;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #4338ca;
}

.error {
    color: red;
    text-align: center;
    margin-bottom: 15px;
    font-size: 14px;
}
</style>
</head>
<body>

<div class="login-card">

    <a href="#" class="servicehub-logo">
        <span class="logo-icon">
            <i class="fas fa-layer-group"></i>
        </span>
        <span class="logo-text">
            Service<span>Hub</span>
        </span>
    </a>

    <h3>Admin Login</h3>

    <?php if (!empty($error)) : ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

</div>

</body>
</html>
