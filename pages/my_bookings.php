<?php
session_start();
require "../config/db.php";

// Only customers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

// Validate order ID
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid booking selected.");
}

$order_id = intval($_GET['order_id']);
$customer_id = $_SESSION['user_id'];

// Fetch booking info with service and merchant details
$stmt = $pdo->prepare("
    SELECT o.*, 
           a.service_date, a.service_time, a.address,a.contact_no,
           s.id AS service_id, 
           s.title AS service_title, 
           s.description AS service_description, 
           s.price AS service_price,
           m.merchant_name
    FROM orders o
    LEFT JOIN appointments a ON a.order_id = o.id
    JOIN services s ON s.id = a.service_id
    JOIN merchants m ON m.id = s.merchant_id
    WHERE o.id = ? AND o.customer_id = ?
");

$stmt->execute([$order_id, $customer_id]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Details | <?= htmlspecialchars($booking['service_title']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #f1f5f9;
        padding: 40px 0;
    }
    .booking-card {
        max-width: 700px;
        margin: auto;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .card-header {
        background: #4361ee;
        color: #fff;
        padding: 30px;
        position: relative;
    }
    .status-pill {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
    }
    .detail-section {
        padding: 30px;
        line-height: 1.6;
    }
    .detail-section h5 {
        margin-top: 20px;
        font-weight: 700;
        color: #4361ee;
    }
    .info-value {
        font-weight: 600;
    }
    .address-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 15px;
        border: 1px solid #e2e8f0;
        margin-top: 5px;
    }
    .btn-modern {
        border-radius: 12px;
        font-weight: 700;
        padding: 10px 20px;
        transition: 0.3s;
    }
    .btn-back {
        margin-top: 20px;
    }
</style>
</head>
<body>

<div class="container">
    <div class="booking-card">
        <div class="card-header">
            <h3 class="fw-bold mb-1"><?= htmlspecialchars($booking['service_title']) ?></h3>
            <p class="mb-0"><i class="bi bi-shop me-1"></i> <?= htmlspecialchars($booking['merchant_name']) ?></p>
            <span class="status-pill"><?= ucfirst($booking['booking_status']) ?></span>
        </div>

        <div class="detail-section">
            <!-- Service Info -->
            <h5>Service Information</h5>
            <p><span class="info-value">Title:</span> <?= htmlspecialchars($booking['service_title']) ?></p>
            <p><span class="info-value">Description:</span><br> <?= nl2br(htmlspecialchars($booking['service_description'])) ?></p>

            <!-- Merchant Info -->
            

            <!-- Appointment Info -->
            <h5>Appointment Details</h5>
            <p><span class="info-value">Date:</span> <?= date('d M, Y', strtotime($booking['service_date'])) ?></p>
            <p><span class="info-value">Time:</span> <?= date('h:i A', strtotime($booking['service_time'])) ?></p>
            <p><span class="info-value">Address:</span></p>
            <p><span class="info-value">Contact Number:</span> 
<?= htmlspecialchars($booking['contact_no']) ?>
</p>

            <div class="address-box"><?= nl2br(htmlspecialchars($booking['address'])) ?></div> 


            <!-- Payment Info -->
            <h5>Payment Information</h5>
            <p><span class="info-value">Status:</span> <?= ucfirst(htmlspecialchars($booking['payment_status'])) ?></p>
            <p><span class="info-value">Amount Paid:</span> ₹<?= number_format($booking['service_price'], 2) ?></p>

            <?php if($booking['payment_status'] === 'pending'): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    Payment is still pending for this service.
                </div>
            <?php else: ?>
                <div class="alert alert-success mt-3">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Payment completed successfully.
                </div>
            <?php endif; ?>

            <a href="../customer/orders.php" class="btn btn-light btn-modern btn-back"><i class="bi bi-arrow-left me-1"></i> Back to My Bookings</a>
        </div>
    </div>
</div>

</body>
</html>
