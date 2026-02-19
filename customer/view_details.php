<?php
session_start();
require '../config/db.php';

// Check if customer logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = "";
$order = null;

// Validate Order ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = "Invalid booking selected.";
} else {

    $order_id = (int) $_GET['id'];
    $customer_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT o.*, m.merchant_name
        FROM orders o
        JOIN merchants m ON o.merchant_id = m.id
        WHERE o.id = ? AND o.customer_id = ?
    ");
    $stmt->execute([$order_id, $customer_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $error = "Booking not found or access denied.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice #BOK-<?= htmlspecialchars($order['id']) ?> | ServiceHub</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --primary: #4361ee;
        --primary-soft: rgba(67, 97, 238, 0.1);
        --success: #10b981;
        --warning: #f59e0b;
        --slate-50: #f8fafc;
        --slate-100: #f1f5f9;
        --slate-400: #94a3b8;
        --slate-800: #1e293b;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f1f5f9;
        background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
        background-size: 20px 20px;
        color: var(--slate-800);
        padding: 60px 20px;
        margin: 0;
    }

    .invoice-container {
        max-width: 600px;
        margin: auto;
        position: relative;
    }

    .invoice-card {
        background: white;
        border-radius: 30px;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid var(--slate-200);
    }

    /* Top Aesthetic Strip */
    .top-bar {
        height: 8px;
        background: linear-gradient(90deg, var(--primary), #4cc9f0);
    }

    .header-section {
        padding: 40px;
        text-align: center;
        border-bottom: 2px dashed var(--slate-100);
        position: relative;
    }

    /* Receipt Cutout Circles */
    .header-section::before, .header-section::after {
        content: '';
        position: absolute;
        bottom: -12px;
        width: 24px;
        height: 24px;
        background: #f1f5f9;
        border-radius: 50%;
    }
    .header-section::before { left: -12px; }
    .header-section::after { right: -12px; }

    .logo-area {
        width: 60px;
        height: 60px;
        background: var(--primary-soft);
        color: var(--primary);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 15px;
    }

    .header-section h2 { margin: 0; font-weight: 800; letter-spacing: -1px; }
    .order-id { color: var(--slate-400); font-weight: 600; font-size: 0.9rem; margin-top: 5px; }

    .content-body { padding: 40px; }

    .data-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }

    .data-item label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--slate-400);
        margin-bottom: 8px;
    }

    .data-item p { margin: 0; font-weight: 700; color: var(--slate-800); font-size: 1rem; }

    /* Badges */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 100px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .s-paid { background: #dcfce7; color: #15803d; }
    .s-pending { background: #fef3c7; color: #b45309; }
    .s-confirmed { background: var(--primary-soft); color: var(--primary); }

    .transaction-box {
        background: var(--slate-50);
        border: 1px solid var(--slate-200);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 32px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
        border-top: 2px solid var(--slate-100);
    }

    .total-label { font-weight: 800; font-size: 1.1rem; }
    .total-amount { font-weight: 900; font-size: 1.8rem; color: var(--primary); }

    .btn-group {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 12px;
        margin-top: 30px;
    }

    .btn {
        padding: 16px;
        border-radius: 16px;
        font-weight: 700;
        text-decoration: none;
        text-align: center;
        transition: 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary { background: var(--primary); color: white; box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 25px rgba(67, 97, 238, 0.3); }
    .btn-secondary { background: white; color: var(--slate-800); border: 1px solid var(--slate-200); }

    /* Print styling */
    @media print {
        body { background: white; padding: 0; }
        .invoice-card { box-shadow: none; border: none; }
        .btn-group, .top-bar { display: none; }
    }
</style>
</head>
<body>

<div class="invoice-container">
    <div class="invoice-card">
        <div class="top-bar"></div>

        <?php if (!empty($error)) : ?>
            <div style="padding:80px 40px; text-align:center;">
                <div class="logo-area" style="color:var(--danger); background:rgba(239, 68, 68, 0.1);">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h2 style="font-weight:800;">Booking Not Found</h2>
                <p class="text-muted">We couldn't retrieve the details for this invoice.</p>
                <a href="dashboard.php" class="btn btn-secondary" style="display:inline-block; margin-top:20px;">Return to Dashboard</a>
            </div>
        <?php else : ?>

            <div class="header-section">
                <div class="logo-area">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <h2>Success!</h2>
                <div class="order-id">TRANSACTION ID: #BOK-<?= htmlspecialchars($order['id']) ?></div>
            </div>

            <div class="content-body">
                <div class="data-grid">
                    <div class="data-item">
                        <label><i class="bi bi-person-badge"></i> Merchant</label>
                        <p><?= htmlspecialchars($order['merchant_name']) ?></p>
                    </div>
                    <div class="data-item">
                        <label><i class="bi bi-calendar3"></i> Date</label>
                        <p><?= date('d M, Y', strtotime($order['created_at'])) ?></p>
                    </div>
                    <div class="data-item">
                        <label><i class="bi bi-info-circle"></i> Service Status</label>
                        <span class="status-pill s-confirmed">
                            <?= ucfirst(htmlspecialchars($order['booking_status'])) ?>
                        </span>
                    </div>
                    <div class="data-item">
                        <label><i class="bi bi-credit-card-2-back"></i> Payment</label>
                        <span class="status-pill <?= strtolower($order['payment_status']) == 'paid' ? 's-paid' : 's-pending' ?>">
                            <?= ucfirst(htmlspecialchars($order['payment_status'])) ?>
                        </span>
                    </div>
                </div>

                <div class="transaction-box">
                    <label style="font-size: 0.65rem; font-weight: 800; color: var(--slate-400); text-transform: uppercase;">Reference Number</label>
                    <p style="font-family: 'Courier New', Courier, monospace; margin: 5px 0 0; font-weight: 700; letter-spacing: 1px;">
                        <?= !empty($order['transaction_id']) ? htmlspecialchars($order['transaction_id']) : 'X X X X - PENDING' ?>
                    </p>
                </div>

                <div class="total-row">
                    <div class="total-label">Amount Paid</div>
                    <div class="total-amount">₹<?= number_format($order['total_amount'], 2) ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($error)) : ?>
    <div class="btn-group">
        <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer-fill me-2"></i> Print Receipt
        </button>
    </div>
    <?php endif; ?>
</div>

</body>
</html>