<?php
require "../includes/auth_check.php";
require "../config/db.php";

/* Allow only customers */
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in customer info
$stmt_user = $pdo->prepare("SELECT name, email FROM users WHERE id=?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

/* --- Stats Logic --- */
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id=?");
$total_stmt->execute([$user_id]);
$total_count = $total_stmt->fetchColumn();


$pending_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders 
    WHERE customer_id=? AND booking_status='Pending'
");
$pending_stmt->execute([$user_id]);
$pending_count = $pending_stmt->fetchColumn();


$completed_stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM orders 
    WHERE customer_id=? AND booking_status='Completed'
");
$completed_stmt->execute([$user_id]);
$completed_count = $completed_stmt->fetchColumn();


/* --- Recent Bookings Logic --- */
$stmt_recent = $pdo->prepare("
    SELECT 
        o.*, 
        s.title AS service_title,
        m.merchant_name,
        a.service_date
    FROM orders o
    JOIN appointments a ON o.id = a.order_id
    JOIN services s ON a.service_id = s.id
    JOIN merchants m ON o.merchant_id = m.id
    WHERE o.customer_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt_recent->execute([$user_id]);
$bookings = $stmt_recent->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard – ServiceHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">


    <style>
        :root {
            --brand-color: #6366f1;
            --brand-hover: #4338ca;
            --bg-light: #f8fafc;
        }

        body { background: var(--bg-light); font-family: 'Inter', sans-serif; }
        
        /* --- ServiceHub Master Logo Design --- */
        /* ===== ServiceHub Logo ===== */
.servicehub-logo {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.logo-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, #6366f1, #4338ca);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.3rem;
    box-shadow: 0 6px 18px rgba(99, 102, 241, 0.35);
}

.logo-text {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-weight: 800;
    font-size: 1.6rem;
    letter-spacing: -0.04em;
    color: #1e293b;
}

.logo-text span {
    color: #6366f1;
}


        /* --- UI Elements --- */
        .card-custom {
            border: none; border-radius: 24px; background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,.04);
        }

        .stat-card {
            border-radius: 24px; border: 1px solid #f1f5f9;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,.08); }

        .icon-box {
            width: 52px; height: 52px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: #fff;
        }

        .btn-action {
            border-radius: 16px; padding: 16px; font-weight: 700;
            transition: 0.3s; border: 1px solid #e2e8f0;
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            text-decoration: none;
        }
        .btn-action:hover { background: #f8fafc; transform: scale(1.02); }
        .btn-primary-action { background: var(--brand-color); color: white; border: none; }
        .btn-primary-action:hover { background: var(--brand-hover); color: white; }

        .status-pill {
            padding: 6px 14px; border-radius: 10px; font-size: 0.75rem; font-weight: 700;
        }
        .status-pending { background: #fff7ed; color: #c2410c; }
        .status-completed { background: #ecfdf5; color: #047857; }
        .status-confirmed { background: #eef2ff; color: #4338ca; }
    </style>
</head>

<body>

<nav class="navbar bg-white border-bottom py-3 sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="servicehub-logo">
    <span class="logo-icon">
        <i class="fas fa-layer-group"></i>
    </span>
    <span class="logo-text">
        Service<span>Hub</span>
    </span>
</a>

        </a>
        <a href="../auth/logout.php" class="btn btn-outline-danger rounded-pill px-4 fw-600">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
    </div>
</nav>

<div class="container py-5">

    <div class="mb-5">
        <h2 class="fw-bold text-dark mb-1">Welcome, <?= htmlspecialchars($user['name']) ?> 👋</h2>
        <p class="text-secondary">Manage your bookings and find expert help today.</p>
    </div>

    <div class="mb-5">
        <h5 class="fw-bold mb-3 px-1">Quick Actions</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <a href="../pages/categories.php" class="btn btn-primary btn-action btn-primary-action shadow-sm">
                    <i class="bi bi-search fs-4"></i> Browse Services
                </a>
            </div>
            <div class="col-md-4">
                <a href="orders.php" class="btn btn-white btn-action text-dark shadow-sm">
                    <i class="bi bi-receipt fs-4 text-primary"></i> My Orders
                </a>
            </div>
            <div class="col-md-4">
                <a href="reviews.php" class="btn btn-white btn-action text-dark shadow-sm">
                    <i class="bi bi-chat-left-text fs-4 text-success"></i> My Reviews
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-custom stat-card p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary me-3 shadow-sm"><i class="bi bi-bag-check"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">Total Orders</h6>
                        <h3 class="fw-bold mb-0"><?= $total_count ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom stat-card p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-warning me-3 shadow-sm"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">Pending</h6>
                        <h3 class="fw-bold mb-0"><?= $pending_count ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom stat-card p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success me-3 shadow-sm"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold mb-1">Completed</h6>
                        <h3 class="fw-bold mb-0"><?= $completed_count ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom overflow-hidden">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <h5 class="fw-bold mb-0">Recent Bookings</h5>
            <a href="orders.php" class="text-primary text-decoration-none fw-bold small">View All Activity</a>
        </div>
        <div class="card-body p-0">
            <?php if(!$bookings): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                    <p>No bookings yet. Find your first expert today!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small">
                                <th class="ps-4 border-0 py-3">SERVICE</th>
                                <th class="border-0 py-3">MERCHANT</th>
                                <th class="border-0 py-3">DATE</th>
                                <th class="pe-4 border-0 py-3">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $b): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($b['service_title']) ?></span>
                                </td>
                                <td class="text-secondary"><?= htmlspecialchars($b['merchant_name']) ?></td>
                                <td class="text-muted"><?= date('d M Y', strtotime($b['service_date'])) ?></td>
                                <td class="pe-4">
                                    <span class="status-pill status-<?= strtolower($b['booking_status']) ?>">
    <?= ucfirst($b['booking_status']) ?>
</span>

                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>