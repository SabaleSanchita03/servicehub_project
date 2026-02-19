<?php
require __DIR__ . "/../includes/auth_check.php";
require __DIR__ . "/../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get merchant ID linked to current user
$stmtMerchant = $pdo->prepare("SELECT id FROM merchants WHERE user_id = ?");
$stmtMerchant->execute([$user_id]);
$merchant_id = $stmtMerchant->fetchColumn();

// Fetch order with customer details
$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    WHERE o.id = ? AND o.merchant_id = ?
");
$stmt->execute([$order_id, $merchant_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found or access denied.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail | ServiceHub Merchant</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --primary: #6366f1;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-500: #64748b;
            --slate-800: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fe;
            color: var(--slate-800);
            padding: 40px 20px;
        }

        .order-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .back-link {
            text-decoration: none;
            color: var(--slate-500);
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--primary); }

        .order-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Header Section */
        .card-header {
            padding: 30px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-id-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--slate-500);
            font-weight: 800;
            margin: 0;
        }

        .order-id-number {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            color: var(--slate-800);
        }

        /* Badge Logic */
        .badge {
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .status-paid { background: #ecfdf5; color: #10b981; }
        .status-pending { background: #fffbeb; color: #f59e0b; }
        .status-confirmed { background: #eef2ff; color: #6366f1; }

        /* Content Sections */
        .card-body { padding: 30px; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-block label {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--slate-500);
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-block p {
            font-weight: 600;
            font-size: 1rem;
            margin: 0;
        }

        .amount-card {
            background: var(--slate-50);
            padding: 25px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-price {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary);
        }

        /* Print Button */
        .actions {
            padding: 20px 30px;
            background: #fafafa;
            border-top: 1px solid #f1f5f9;
            text-align: right;
        }

        .btn-print {
            background: white;
            border: 1px solid #e2e8f0;
            color: var(--slate-800);
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-print:hover { background: var(--slate-100); }

        @media (max-width: 600px) {
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="order-container">
    <a href="orders.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>

    <div class="order-card">
        <div class="card-header">
            <div>
                <p class="order-id-label">Order Reference</p>
                <h1 class="order-id-number">#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h1>
            </div>
            <span class="badge status-confirmed">
                <i class="fas fa-circle-check me-1"></i> <?= $order['booking_status'] ?>
            </span>
        </div>

        <div class="card-body">
            <div class="info-grid">
                <div class="info-block">
                    <label>Customer Details</label>
                    <p><?= htmlspecialchars($order['customer_name']) ?></p>
                    <small style="color:var(--slate-500)"><?= htmlspecialchars($order['customer_email']) ?></small>
                </div>

                <div class="info-block">
                    <label>Booking Date</label>
                    <p><?= date('d M Y', strtotime($order['created_at'])) ?></p>
                    <small style="color:var(--slate-500)"><?= date('h:i A', strtotime($order['created_at'])) ?></small>
                </div>

                <div class="info-block">
                    <label>Payment Status</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="badge <?= strtolower($order['payment_status']) == 'paid' ? 'status-paid' : 'status-pending' ?>">
                            <?= strtoupper($order['payment_status']) ?>
                        </span>
                    </div>
                </div>

                <div class="info-block">
                    <label>Fulfillment</label>
                    <p>Standard Service</p>
                </div>
            </div>

            <div class="amount-card">
                <div>
                    <label class="order-id-label" style="margin-bottom: 5px; display:block;">Total Earnings</label>
                    <span style="color:var(--slate-500); font-size:0.85rem; font-weight:600;">Includes all taxes & fees</span>
                </div>
                <div class="total-price">₹<?= number_format($order['total_amount'], 2) ?></div>
            </div>
        </div>

        <div class="actions">
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print Invoice
            </button>
        </div>
    </div>
</div>

</body>
</html>