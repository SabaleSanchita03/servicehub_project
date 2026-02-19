<?php
require "../includes/auth_check.php";
require "../config/db.php";

$user_id = $_SESSION['user_id'];

// Get merchant info
$stmt = $pdo->prepare("
    SELECT m.id AS merchant_id, u.name AS user_name
    FROM merchants m
    JOIN users u ON m.user_id = u.id
    WHERE m.user_id = ?
");
$stmt->execute([$user_id]);
$merchant = $stmt->fetch();
if (!$merchant) die("Merchant profile not found.");

$merchant_id = $merchant['merchant_id'];

// Stats Logic (Keeping your existing logic)
$total_services = $pdo->prepare("SELECT COUNT(*) FROM services WHERE merchant_id = ?");
$total_services->execute([$merchant_id]);
$total_services = $total_services->fetchColumn();

$total_orders_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders 
    WHERE merchant_id = ?
");
$total_orders_stmt->execute([$merchant_id]);
$total_orders = $total_orders_stmt->fetchColumn();


$total_earnings_stmt = $pdo->prepare("
    SELECT IFNULL(SUM(total_amount),0)
    FROM orders
    WHERE merchant_id = ?
    AND payment_status = 'paid'
");
$total_earnings_stmt->execute([$merchant_id]);
$total_earnings = $total_earnings_stmt->fetchColumn();
 
// Pending Bookings
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM orders
    WHERE merchant_id = ?
    AND booking_status = 'Pending'
");
$stmt->execute([$merchant_id]);
$pending_bookings = $stmt->fetchColumn();

// Today's Bookings
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders o
    JOIN appointments a ON a.order_id = o.id
    WHERE o.merchant_id = ?
    AND DATE(a.service_date) = CURDATE()
");
$stmt->execute([$merchant_id]);
$today_bookings = $stmt->fetchColumn();

// Average Rating
$stmt = $pdo->prepare("
    SELECT IFNULL(AVG(r.rating),0)
    FROM reviews r
    JOIN orders o ON r.order_id = o.id
    WHERE o.merchant_id = ?
");
$stmt->execute([$merchant_id]);
$avg_rating = number_format($stmt->fetchColumn(), 1);

// Recent Orders
$recentOrdersStmt = $pdo->prepare("
    SELECT 
        o.id,
        u.name AS customer_name,
        s.title AS service_name,
        o.total_amount,
        o.booking_status,
        a.service_date,
        a.service_time,
        o.created_at,
        r.rating
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    JOIN appointments a ON a.order_id = o.id
    JOIN services s ON s.id = a.service_id
    LEFT JOIN reviews r ON r.order_id = o.id
    WHERE o.merchant_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");

$recentOrdersStmt->execute([$merchant_id]);
$recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchant Dashboard | ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-accent: #1e293b;
            --primary: #6366f1;
            --primary-light: rgba(99, 102, 241, 0.1);
            --bg-main: #f8fafc;
            --glass-border: rgba(226, 232, 240, 0.8);
            --text-heading: #1e293b;
            --text-body: #64748b;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-main); 
            color: var(--text-heading);
            min-height: 100vh;
        }

        /* Dark Sidebar Layout */
        .sidebar { 
            width: 280px; 
            height: 100vh; 
            background: var(--sidebar-bg); 
            position: fixed; 
            left: 0; top: 0;
            padding: 1.5rem; 
            z-index: 1000;
            border-right: 1px solid var(--sidebar-accent);
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.5rem 0.75rem;
            margin-bottom: 2.5rem;
            text-decoration: none;
        }

        .logo-sq {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #818cf8, #4f46e5);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .logo-text-light { font-weight: 800; font-size: 1.4rem; color: #fff; letter-spacing: -0.5px; }
        .logo-text-light span { color: #818cf8; }

        .nav-menu { list-style: none; padding: 0; }
        .nav-item-link {
            display: flex; align-items: center;
            padding: 0.85rem 1rem;
            color: #94a3b8;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
        }

        .nav-item-link i { font-size: 1.25rem; margin-right: 12px; }
        .nav-item-link:hover, .nav-item-link.active {
            color: #fff;
            background: var(--sidebar-accent);
        }
        .nav-item-link.active { border-left: 4px solid var(--primary); }

        /* Content Area */
        .content-wrapper { margin-left: 280px; padding: 2rem 3rem; }

        /* Modern Stat Cards */
        .stat-group-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--glass-border);
            display: flex; align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .stat-group-card:hover { transform: translateY(-3px); }

        .stat-icon-circle {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .val-num { font-size: 1.5rem; font-weight: 800; color: var(--text-heading); margin: 0; }
        .val-label { font-size: 0.75rem; font-weight: 700; color: var(--text-body); text-transform: uppercase; letter-spacing: 0.5px; }

        /* Table UI */
        .data-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            padding: 1.75rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
        }

        .custom-table { border-collapse: separate; border-spacing: 0 8px; width: 100%; }
        .custom-table thead th {
            color: var(--text-body);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .custom-table tbody tr { background: #fff; transition: 0.2s; }
        .custom-table tbody tr:hover { background: #f8fafc; }
        .custom-table td { padding: 1.25rem 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

        /* Customer Profile */
        .client-info { display: flex; align-items: center; gap: 12px; }
        .client-initial {
            width: 36px; height: 36px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.85rem;
        }

        /* Status Pills */
        .status-pill {
            padding: 6px 12px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .st-completed { background: #dcfce7; color: #15803d; }
        .st-pending { background: #fef9c3; color: #854d0e; }
        .st-cancelled { background: #fee2e2; color: #b91c1c; }

        /* Action Buttons */
        .btn-action-primary {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            transition: 0.3s;
        }
        .btn-action-primary:hover { background: #4f46e5; transform: scale(1.02); color: white;}

        @media (max-width: 1100px) {
            .sidebar { width: 85px; padding: 1rem; }
            .logo-text-light, .nav-item-link span { display: none; }
            .content-wrapper { margin-left: 85px; padding: 1.5rem; }
            .nav-item-link i { margin-right: 0; width: 100%; text-align: center; font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <a href="index.php" class="logo-box">
        <div class="logo-sq"><i class="fas fa-layer-group"></i></div>
        <span class="logo-text-light">Service<span>Hub</span></span>
    </a>
    <ul class="nav-menu">
        <li><a href="index.php" class="nav-item-link active"><i class="bi bi-grid"></i> <span>Dashboard</span></a></li>
        <li><a href="services.php" class="nav-item-link"><i class="bi bi-briefcase"></i> <span>Services</span></a></li>
        <li><a href="orders.php" class="nav-item-link"><i class="bi bi-calendar-check"></i> <span>Orders</span></a></li>
        <li><a href="profile.php" class="nav-item-link"><i class="bi bi-person-circle"></i> <span>Profile</span></a></li>
    </ul>
    <div style="position: absolute; bottom: 2rem; width: calc(100% - 3rem);">
        <a href="../auth/logout.php" class="nav-item-link text-danger"><i class="bi bi-box-arrow-left"></i> <span>Sign Out</span></a>
    </div>
</aside>

<main class="content-wrapper">
    <header class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <div>
            <h1 class="val-num mb-1">Welcome, <?= htmlspecialchars($merchant['user_name']) ?> 👋</h1>
            <p class="text-body fw-500 m-0">Here's what's happening with your business today.</p>
        </div>
        <a href="services.php" class="btn btn-action-primary mt-3 mt-md-0">
            <i class="bi bi-plus-lg me-2"></i> Create Service
        </a>
    </header>

    <div class="row g-4 mb-5">
        <div class="col-xl-4 col-sm-6">
            <div class="stat-group-card">
                <div class="stat-icon-circle bg-primary bg-opacity-10 text-primary"><i class="bi bi-wallet2"></i></div>
                <div>
                    <p class="val-label">Revenue</p>
                    <h3 class="val-num">₹<?= number_format($total_earnings, 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="stat-group-card">
                <div class="stat-icon-circle bg-warning bg-opacity-10 text-warning"><i class="bi bi-calendar4-week"></i></div>
                <div>
                    <p class="val-label">Orders</p>
                    <h3 class="val-num"><?= $total_orders ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="stat-group-card">
                <div class="stat-icon-circle bg-danger bg-opacity-10 text-danger"><i class="bi bi-clock-history"></i></div>
                <div>
                    <p class="val-label">Pending</p>
                    <h3 class="val-num"><?= $pending_bookings ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="stat-group-card">
                <div class="stat-icon-circle bg-info bg-opacity-10 text-info"><i class="bi bi-lightning-charge"></i></div>
                <div>
                    <p class="val-label">Today</p>
                    <h3 class="val-num"><?= $today_bookings ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="stat-group-card">
                <div class="stat-icon-circle bg-warning bg-opacity-10 text-warning"><i class="bi bi-star-fill"></i></div>
                <div>
                    <p class="val-label">Rating</p>
                    <h3 class="val-num"><?= $avg_rating ?> <small style="font-size: 1rem; color: #a3aed0;">/ 5</small></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-800 m-0">Recent Activity</h4>
            <a href="orders.php" class="btn btn-sm btn-light fw-bold px-3 rounded-pill text-primary">Explore All</a>
        </div>
        
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Booking Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($recentOrders): ?>
                        <?php foreach($recentOrders as $o):
                            $stClass = 'st-pending';
                            if(strtolower($o['booking_status'])=='completed') $stClass='st-completed';
                            if(strtolower($o['booking_status'])=='cancelled') $stClass='st-cancelled';
                        ?>
                        <tr>
                            <td>
                                <div class="client-info">
                                    <div class="client-initial"><?= strtoupper(substr($o['customer_name'], 0, 1)) ?></div>
                                    <div class="fw-700"><?= htmlspecialchars($o['customer_name']) ?></div>
                                </div>
                            </td>
                            <td class="text-body fw-500"><?= htmlspecialchars($o['service_name']) ?></td>
                            <td>
                                <div class="fw-700"><?= date('M d, Y', strtotime($o['service_date'])) ?></div>
                                <div class="text-body small"><?= $o['service_time'] ?></div>
                            </td>
                            <td class="fw-800">₹<?= number_format($o['total_amount'], 2) ?></td>
                            <td><span class="status-pill <?= $stClass ?>"><?= $o['booking_status'] ?></span></td>
                            <td>
                                <?php if($o['rating']): ?>
                                    <span class="text-warning fw-bold"><i class="bi bi-star-fill me-1"></i><?= $o['rating'] ?></span>
                                <?php else: ?>
                                    <span class="text-body small opacity-50">No review</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-body fw-500">No activity recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>