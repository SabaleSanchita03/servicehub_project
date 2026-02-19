<?php
// ... [KEEP YOUR PHP LOGIC EXACTLY AS IT WAS] ...
require __DIR__ . "/../includes/auth_check.php";
require __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmtMerchant = $pdo->prepare("SELECT id FROM merchants WHERE user_id = ?");
$stmtMerchant->execute([$user_id]);
$merchant_id = $stmtMerchant->fetchColumn();

if (!$merchant_id) {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $stmt = $pdo->prepare("UPDATE orders SET booking_status = ? WHERE id = ? AND merchant_id = ?");
    $stmt->execute([$new_status, $order_id, $merchant_id]);
    header("Location: orders.php");
    exit;
}

$stmt = $pdo->prepare("SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.customer_id = u.id WHERE o.merchant_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$merchant_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalOrders = count($orders);
$totalRevenue = 0;
foreach ($orders as $o) {
    if (strtolower($o['payment_status'] ?? '') === 'paid') {
        $totalRevenue += $o['total_amount'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management | ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background: var(--background);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        /* Modern Navbar */
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .servicehub-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .logo-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--text-main);
        }

        .logo-text span { color: var(--primary); }

        /* Stats Cards */
        .stat-group {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .stat-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Search Bar */
        .search-container {
            position: relative;
            min-width: 300px;
        }

        .search-container i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-input {
            padding-left: 40px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            height: 42px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .search-input:focus {
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
        }

        /* Table Design */
        .main-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .table thead th {
            background: #f8fafc;
            padding: 1.25rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border: none;
        }

        .table tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Status Styling */
        .badge-modern {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-paid { background: #ecfdf5; color: #059669; }
        .status-unpaid { background: #fff1f2; color: #e11d48; }

        /* Form Controls */
        .status-select {
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            border: none;
        }

        .btn-save { background: var(--primary); color: white; }
        .btn-save:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #eef2ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
    </style>
</head>
<body>

<nav class="glass-nav mb-4">
    <div class="container-fluid px-lg-5 d-flex justify-content-between align-items-center">
        <a href="index.php" class="servicehub-logo">
            <span class="logo-icon"><i class="fas fa-layer-group"></i></span>
            <span class="logo-text">Service<span>Hub</span></span>
        </a>
        
        <div class="d-flex align-items-center gap-3">
            <div class="search-container d-none d-md-block">
                <i class="bi bi-search"></i>
                <input type="text" id="orderSearch" class="form-control search-input" placeholder="Search customer or Order ID...">
            </div>
            <a href="dashboard.php" class="btn btn-dark btn-sm rounded-3 px-3 fw-medium">
                <i class="bi bi-grid-1x2 me-2"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-lg-5">
    <div class="mb-5">
        <h2 class="fw-800 mb-1" style="font-family: 'Plus Jakarta Sans';">Orders Management</h2>
        <p class="text-muted">Overview of your service bookings and revenue</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4 col-xl-3">
            <div class="stat-group">
                <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Total Orders</div>
                    <div class="h4 fw-bold mb-0"><?= number_format($totalOrders) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-3">
            <div class="stat-group">
                <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Revenue</div>
                    <div class="h4 fw-bold mb-0">₹<?= number_format($totalRevenue, 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="main-card">
        <div class="table-responsive">
            <table class="table mb-0" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Placed On</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Booking Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): 
                        $bStatus = strtolower($o['booking_status'] ?? 'pending');
                        $statusColor = match($bStatus) {
                            'completed' => 'background:#dcfce7; color:#166534;',
                            'cancelled' => 'background:#fee2e2; color:#991b1b;',
                            'rejected'  => 'background:#f1f5f9; color:#475569;',
                            default     => 'background:#fef9c3; color:#854d0e;',
                        };
                    ?>
                    <tr>
                        <td><span class="fw-bold text-primary">#ORD-<?= $o['id'] ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle"><?= strtoupper(substr($o['customer_name'],0,1)) ?></div>
                                <div>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($o['customer_name']) ?></div>
                                    <div class="text-muted small">Standard Client</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium"><?= date('M d, Y', strtotime($o['created_at'])) ?></div>
                            <div class="text-muted small"><?= date('h:i A', strtotime($o['created_at'])) ?></div>
                        </td>
                        <td><span class="fw-bold text-dark">₹<?= number_format($o['total_amount'], 2) ?></span></td>
                        <td>
                            <span class="badge-modern <?= strtolower($o['payment_status']??'') == 'paid' ? 'status-paid' : 'status-unpaid' ?>">
                                <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                <?= ucfirst($o['payment_status'] ?? 'Pending') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-modern" style="<?= $statusColor ?>">
                                <?= ucfirst($o['booking_status'] ?? 'Pending') ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <form method="POST" class="d-flex justify-content-end gap-2 align-items-center">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="new_status" class="status-select">
                                    <?php foreach(['Pending','Completed','Cancelled','Rejected'] as $status): ?>
                                        <option value="<?= $status ?>" <?= ($o['booking_status']??'')==$status?'selected':'' ?>><?= $status ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn-action btn-save">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <a href="view.php?id=<?= $o['id'] ?>" class="btn-action bg-light text-dark border">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if(empty($orders)): ?>
            <div class="text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Empty" style="width: 80px; opacity: 0.5;" class="mb-3">
                <h5 class="text-muted fw-normal">No orders found</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Intelligent Live Search
    document.getElementById('orderSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#ordersTable tbody tr');
        
        rows.forEach(row => {
            const match = row.innerText.toLowerCase().includes(term);
            row.style.display = match ? '' : 'none';
        });
    });
</script>

</body>
</html>