<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? $page_title : "Admin Panel | ServiceHub" ?></title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<style>
body {
    margin: 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: #f1f5f9;
    display: flex;
}

/* ===== Sidebar ===== */
.sidebar {
    width: 240px;
    background: #1e293b;
    color: white;
    min-height: 100vh;
    padding: 25px 20px;
}

.servicehub-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: white;
    margin-bottom: 30px;
}

.logo-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #4338ca);
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-text {
    font-weight: 800;
    font-size: 1.3rem;
}

.logo-text span {
    color: #6366f1;
}

.nav-links a {
    display: block;
    padding: 10px 12px;
    border-radius: 8px;
    text-decoration: none;
    color: #cbd5e1;
    margin-bottom: 8px;
    font-size: 14px;
    transition: 0.2s;
}

.nav-links a:hover {
    background: #334155;
    color: white;
}

/* ===== Main Content ===== */
.main-content {
    flex: 1;
    padding: 30px 40px;
}
</style>
</head>
<body>

<div class="sidebar">

    <a href="dashboard.php" class="servicehub-logo">
        <span class="logo-icon">
            <i class="fas fa-layer-group"></i>
        </span>
        <span class="logo-text">
            Service<span>Hub</span>
        </span>
    </a>

<div class="nav-links">
    <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="manage_categories.php"><i class="fas fa-list"></i> Categories</a>
    <a href="merchants.php"><i class="fas fa-store"></i> Merchants</a>
    <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
    <a href="orders.php"><i class="fas fa-box"></i> Orders</a>
    <a href="services.php"><i class="fas fa-concierge-bell"></i> Services</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>


</div>

<div class="main-content">
