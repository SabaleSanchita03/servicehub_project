<?php
session_start();
require "../config/db.php";

// Ensure merchant is logged in
if(!isset($_SESSION['merchant_id'])){
    header("Location: login.php");
    exit;
}

$merchant_id = $_SESSION['merchant_id'];

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($booking_id <= 0){
    die("Invalid booking ID.");
}

// Fetch booking details for this merchant
$stmt = $pdo->prepare("
    SELECT 
        b.booking_id,
        b.service_date,
        b.service_time,
        b.address,
        b.contact_number,
        b.booking_status,
        b.payment_status,
        s.title AS service_title,
        s.price AS service_price,
        m.merchant_name AS business_name,
        u.name AS customer_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN merchants m ON s.merchant_id = m.id
    JOIN users u ON b.user_id = u.id
    WHERE b.booking_id = ? AND m.id = ?
");
$stmt->execute([$booking_id, $merchant_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found or you do not have access.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Details – Merchant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }
        .main-card { background:#fff; border-radius:24px; border:1px solid #e2e8f0; box-shadow:0 10px 30px -10px rgba(0,0,0,0.05); overflow:hidden; }
        .info-group { padding:1.25rem; border-bottom:1px solid #f1f5f9; }
        .info-label { font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#94a3b8; margin-bottom:0.5rem; display:block; }
        .info-value { font-size:1rem; font-weight:600; margin-bottom:0; }
        .status-pill { padding:6px 16px; border-radius:100px; font-weight:700; display:inline-block; }
        .status-pending { background:#fff7ed;color:#c2410c; }
        .status-completed { background:#f0fdf4;color:#166534; }
        .status-cancelled { background:#fef2f2;color:#b91c1c; }
        .status-rejected { background:#f1f5f9;color:#475569; }
        .price-display { background:#f8fafc; padding:2rem; border-radius:20px; text-align:center; }
    </style>
</head>
<body>
<div class="container py-5">
    <a href="orders.php" class="btn btn-light mb-3"><i class="bi bi-arrow-left"></i> Back to Orders</a>
    <div class="main-card">
        <div class="p-4 border-bottom bg-light bg-opacity-50">
            <h3 class="fw-bold mb-1"><?= htmlspecialchars($booking['service_title']) ?></h3>
            <p class="text-muted mb-0">Provided by <strong><?= htmlspecialchars($booking['business_name']) ?></strong></p>
        </div>
        <div class="row g-0">
            <div class="col-md-7 border-end">
                <div class="info-group">
                    <span class="info-label">Date & Time</span>
                    <p class="info-value"><?= date('D, d M Y', strtotime($booking['service_date'])) ?> • <?= htmlspecialchars($booking['service_time']) ?></p>
                </div>
                <div class="info-group">
                    <span class="info-label">Service Address</span>
                    <p class="info-value"><?= htmlspecialchars($booking['address']) ?></p>
                </div>
                <div class="info-group">
                    <span class="info-label">Customer Contact</span>
                    <p class="info-value"><?= htmlspecialchars($booking['contact_number']) ?></p>
                </div>
            </div>
            <div class="col-md-5">
                <div class="info-group">
                    <span class="info-label">Booking Status</span>
                    <span class="status-pill status-<?= strtolower($booking['booking_status']) ?>"><?= ucfirst($booking['booking_status']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Payment Status</span>
                    <p class="info-value"><?= ucfirst($booking['payment_status']) ?></p>
                </div>
                <div class="p-4">
                    <div class="price-display">
                        <span class="info-label">Total Amount</span>
                        <h2 class="fw-bold">₹<?= number_format($booking['service_price'], 2) ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
