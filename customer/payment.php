<?php
require "../includes/auth_check.php";
require "../config/db.php";

/* Only customers can access */
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Get booking ID from URL */
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

/* Fetch booking info */
$stmt = $pdo->prepare("
    SELECT b.booking_id, b.payment_status, s.title AS service_title, s.price AS service_price
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found or access denied.");
}

/* If already paid, redirect back */
if ($booking['payment_status'] === 'Paid') {
    header("Location: orders.php");
    exit;
}

/* Handle payment simulation */
if (isset($_POST['pay_now'])) {
    // Normally integrate payment gateway here
    $stmt_update = $pdo->prepare("UPDATE bookings SET payment_status='Paid' WHERE booking_id=?");
    $stmt_update->execute([$booking_id]);

    header("Location: booking_details.php?id=$booking_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment – ServiceHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --success: #2ec4b6;
            --bg-body: #f1f5f9;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: #1e293b;
        }

        /* Centered Payment Container */
        .payment-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Card Styling */
        .payment-card {
            background: #fff;
            border-radius: 24px;
            border: none;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }

        .payment-header {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: #fff;
        }

        /* Order Summary Box */
        .order-summary {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border: 1px dashed #e2e8f0;
        }

        .price-total {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            display: block;
        }

        /* Secure Badge */
        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #ecfdf5;
            color: #059669;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .btn-pay {
            background-color: var(--primary);
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(67, 97, 238, 0.4);
        }

        .btn-pay:hover {
            background-color: #3751d7;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -5px rgba(67, 97, 238, 0.5);
        }

        .back-link {
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-link:hover { color: #1e293b; }
    </style>
</head>
<body>

<div class="payment-wrapper">
    <div class="payment-card">
        <div class="payment-header">
            <div class="mb-2">
                <i class="bi bi-shield-lock-fill fs-1"></i>
            </div>
            <h4 class="fw-bold mb-0">Secure Checkout</h4>
            <p class="opacity-75 small">ServiceHub Payment Gateway</p>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="text-center">
                <span class="secure-badge">
                    <i class="bi bi-patch-check-fill"></i> SSL SECURED
                </span>
                
                <h5 class="text-muted small text-uppercase fw-bold mb-1">Service to Pay</h5>
                <h4 class="fw-bold mb-3"><?= htmlspecialchars($booking['service_title']) ?></h4>
                
                <div class="order-summary">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Booking Amount</span>
                        <span class="fw-bold">₹<?= number_format($booking['service_price'], 2) ?></span>
                    </div>
                    <hr class="my-2 opacity-10">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Total Payable</span>
                        <span class="price-total">₹<?= number_format($booking['service_price'], 2) ?></span>
                    </div>
                </div>

                <form method="POST">
                    <button type="submit" name="pay_now" class="btn btn-primary btn-pay w-100 mb-3">
                        <i class="bi bi-credit-card-fill me-2"></i> Confirm & Pay
                    </button>
                </form>

                <a href="orders.php" class="back-link">
                    <i class="bi bi-arrow-left me-1"></i> Cancel & Go Back
                </a>
            </div>
            
            <div class="mt-5 pt-3 border-top text-center">
                <p class="text-muted" style="font-size: 0.7rem;">
                    <i class="bi bi-lock-fill me-1"></i> Your payment information is encrypted and secure.
                </p>
                <div class="d-flex justify-content-center gap-3 opacity-50 grayscale" style="font-size: 1.5rem;">
                    <i class="bi bi-credit-card"></i>
                    <i class="bi bi-bank"></i>
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>