<?php
require 'includes/auth.php';
require '../config/db.php';

$page_title = "Dashboard";

// Fetch counts
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalMerchants  = $pdo->query("SELECT COUNT(*) FROM merchants")->fetchColumn();
$totalCustomers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalOrders     = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

require 'includes/header.php';
?>
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-top: 30px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .icon-box {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    /* Card Variations */
    .bg-blue { background: #eff6ff; color: #3b82f6; }
    .bg-purple { background: #f5f3ff; color: #8b5cf6; }
    .bg-orange { background: #fff7ed; color: #f97316; }
    .bg-green { background: #ecfdf5; color: #10b981; }

    .stat-info h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }

    .stat-info p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>

<div class="dashboard-welcome">
    <h2 style="font-weight: 800; letter-spacing: -1px;">Welcome back, <?= $_SESSION['admin_username'] ?? 'Admin'; ?> 👋</h2>
    <p style="color: #64748b;">Here is a summary of your marketplace performance.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="icon-box bg-purple">
            <i class="fas fa-th-large"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalCategories ?></h3>
            <p>Categories</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="icon-box bg-blue">
            <i class="fas fa-store"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalMerchants ?></h3>
            <p>Total Merchants</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="icon-box bg-orange">
            <i class="fas fa-user-friends"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalCustomers ?></h3>
            <p>Customers</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="icon-box bg-green">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalOrders ?></h3>
            <p>Service Orders</p>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>