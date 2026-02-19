<?php
require "../includes/auth_check.php";
require "../config/db.php";

// Allow only customers
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch booking details
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
        u.name AS customer_name,
        m.merchant_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.user_id = u.id
    JOIN merchants m ON s.merchant_id = m.id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found or access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Summary – ServiceHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #f0f3ff;
            --success: #10b981;
            --slate-50: #f8fafc;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-700: #334155;
            --slate-900: #0f172a;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #f4f7fa; 
            color: var(--slate-900);
            -webkit-font-smoothing: antialiased;
        }

        /* Modern Header */
        .details-header { 
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            padding: 1rem 0; 
            border-bottom: 1px solid var(--slate-200); 
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* Main Ticket-Style Card */
        .ticket-card { 
            background: #fff; 
            border-radius: 32px; 
            border: 1px solid var(--slate-200); 
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05); 
            overflow: hidden; 
        }

        /* Gradient Service Banner */
        .service-banner {
            background: linear-gradient(135deg, var(--primary) 0%, #7209b7 100%);
            padding: 2.5rem;
            color: white;
            position: relative;
        }
        .service-banner::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 20px;
            background: #fff;
            clip-path: polygon(0 100%, 5% 0, 10% 100%, 15% 0, 20% 100%, 25% 0, 30% 100%, 35% 0, 40% 100%, 45% 0, 50% 100%, 55% 0, 60% 100%, 65% 0, 70% 100%, 75% 0, 80% 100%, 85% 0, 90% 100%, 95% 0, 100% 100%);
        }

        /* Info Groups */
        .info-tile {
            padding: 1.5rem;
            border-radius: 20px;
            background: var(--slate-50);
            height: 100%;
            border: 1px solid transparent;
            transition: 0.2s;
        }
        .info-tile:hover {
            background: #fff;
            border-color: var(--slate-200);
            transform: translateY(-2px);
        }

        .label-caps {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--slate-400);
            display: block;
            margin-bottom: 0.5rem;
        }

        /* Status Pills */
        .status-pill {
            padding: 8px 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .status-confirmed { background: #e0e7ff; color: #4338ca; }
        .status-pending { background: #ffedd5; color: #9a3412; }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }

        /* Price Display */
        .amount-card {
            background: var(--slate-900);
            color: white;
            border-radius: 24px;
            padding: 2rem;
            text-align: center;
        }

        /* Button Styling */
        .btn-modern {
            border-radius: 16px;
            padding: 14px 28px;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn-primary-glow {
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 8px 20px -6px rgba(67, 97, 238, 0.5);
        }
        .btn-primary-glow:hover {
            background: #3046c8;
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .border-end { border-end: none !important; border-bottom: 1px solid var(--slate-200); }
        }
    </style>
</head>
<body>

<header class="details-header">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="orders.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">
            <i class="bi bi-chevron-left"></i> My Bookings
        </a>
        <h6 class="mb-0 fw-800 text-uppercase small tracking-widest">Receipt Summary</h6>
        <button class="btn btn-light btn-sm rounded-circle" onclick="window.print()"><i class="bi bi-printer"></i></button>
    </div>
</header>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="ticket-card">
                
                <div class="service-banner text-center">
                    <div class="mb-2"><i class="bi bi-patch-check-fill fs-2"></i></div>
                    <h2 class="fw-800 mb-1"><?= htmlspecialchars($booking['service_title']) ?></h2>
                    <p class="mb-0 opacity-75">Booking Reference: #BOK-<?= htmlspecialchars($booking['booking_id']) ?></p>
                </div>

                <div class="p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-md-7 border-end pe-md-5">
                            <div class="mb-5">
                                <span class="label-caps">Merchant Information</span>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary-light text-primary rounded-3 p-3">
                                        <i class="bi bi-shop fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5"><?= htmlspecialchars($booking['merchant_name']) ?></div>
                                        <div class="text-muted small">Professional Service Partner</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="info-tile">
                                        <span class="label-caps">Date & Time Slot</span>
                                        <div class="fw-bold text-dark">
                                            <i class="bi bi-calendar3 me-2 text-primary"></i>
                                            <?= date('D, d M Y', strtotime($booking['service_date'])) ?>
                                            <span class="mx-2 text-slate-200">|</span>
                                            <?= htmlspecialchars($booking['service_time']) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-tile">
                                        <span class="label-caps">Service Location</span>
                                        <div class="fw-bold text-dark d-flex gap-2">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <span><?= htmlspecialchars($booking['address']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-tile">
                                        <span class="label-caps">Primary Contact</span>
                                        <div class="fw-bold text-dark">
                                            <i class="bi bi-telephone-fill me-2 text-success"></i>
                                            <?= htmlspecialchars($booking['contact_number']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5 ps-md-5">
                            <div class="mb-5">
                                <span class="label-caps">Status Details</span>
                                <div class="status-pill status-<?= strtolower($booking['booking_status']) ?> mb-3">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <?= ucfirst($booking['booking_status']) ?>
                                </div>
                                <div class="d-flex align-items-center text-success fw-bold small">
                                    <i class="bi bi-shield-check me-2"></i> Verified Payment: <?= ucfirst($booking['payment_status']) ?>
                                </div>
                            </div>

                            <div class="amount-card mb-5">
                                <span class="label-caps text-white-50">Grand Total Paid</span>
                                <div class="display-6 fw-800">₹<?= number_format($booking['service_price'], 2) ?></div>
                                <div class="small opacity-50 mt-1">Inclusive of all taxes</div>
                            </div>

                            <div class="d-grid gap-3">
                                <a href="orders.php" class="btn btn-primary-glow btn-modern">Return to Dashboard</a>
                                <button class="btn btn-outline-secondary btn-modern border-0 text-muted" onclick="window.print()">
                                    <i class="bi bi-download me-2"></i> Save PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-4 text-center border-top">
                    <p class="text-muted small mb-0">
                        Thank you for choosing <strong>ServiceHub</strong>. If you have any issues with this booking, please contact our 24/7 help center.
                    </p>
                </div>
            </div>

            <div class="text-center mt-5">
                <div class="text-slate-400 small">Copyright &copy; <?= date('Y') ?> ServiceHub Inc. All rights reserved.</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>