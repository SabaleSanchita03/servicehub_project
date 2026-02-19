<?php
require "../includes/auth_check.php";
require "../config/db.php";

if ($_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* -------------------------
   CANCEL BOOKING
--------------------------*/
if (isset($_GET['cancel_id'])) {
    $cancel_id = intval($_GET['cancel_id']);

    $stmt = $pdo->prepare("
        SELECT booking_status 
        FROM orders 
        WHERE id = ? AND customer_id = ?
    ");
    $stmt->execute([$cancel_id, $user_id]);
    $order = $stmt->fetch();

    if ($order && in_array($order['booking_status'], ['Pending','Confirmed'])) {
        $pdo->prepare("
            UPDATE orders 
            SET booking_status = 'Cancelled' 
            WHERE id = ?
        ")->execute([$cancel_id]);

        $_SESSION['message'] = "Booking #$cancel_id cancelled successfully.";
    }

    header("Location: orders.php");
    exit;
}

/* -------------------------
   HIDE CARD (X BUTTON)
--------------------------*/
if (isset($_GET['hide_id'])) {
    $hide_id = intval($_GET['hide_id']);

    $pdo->prepare("
        UPDATE orders 
        SET is_hidden = 1 
        WHERE id = ? AND customer_id = ?
    ")->execute([$hide_id, $user_id]);

    header("Location: orders.php");
    exit;
}

/* -------------------------
   FETCH ORDERS
--------------------------*/
$status_filter = $_GET['status'] ?? '';
$status_filter = ucfirst(strtolower($status_filter));

$query = "
    SELECT 
        o.id,
        o.booking_status,
        o.payment_status,
        o.total_amount,
        o.created_at,
        a.service_date,
        a.service_time,
        s.title AS service_title,
        s.price AS service_price,
        m.merchant_name
    FROM orders o
    JOIN appointments a ON o.id = a.order_id
    JOIN services s ON a.service_id = s.id
    JOIN merchants m ON o.merchant_id = m.id
    WHERE o.customer_id = ?
    AND o.is_hidden = 0
";

$params = [$user_id];

if ($status_filter && in_array($status_filter, ['Pending','Completed','Confirmed','Cancelled'])) {
    $query .= " AND o.booking_status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders – ServiceHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --bg-body: #f8fafc;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-700: #334155;
            --slate-900: #0f172a;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-body); 
            color: var(--slate-900);
            -webkit-font-smoothing: antialiased;
        }

        /* Floating Header Styling */
        .page-header {
            background: #ffffff;
            padding: 2.5rem 0;
            border-bottom: 1px solid var(--slate-200);
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px -10px rgba(0,0,0,0.05);
        }

        /* Filter Tabs Customization */
        .nav-pills {
            background: #fff;
            padding: 6px;
            border-radius: 16px;
            display: inline-flex;
            border: 1px solid var(--slate-200);
        }
        .nav-pills .nav-link {
            color: var(--slate-400);
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 24px;
            border-radius: 12px;
            transition: 0.2s;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 8px 15px -5px rgba(67, 97, 238, 0.4);
        }

        /* Booking Card Refinement */
        .booking-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid var(--slate-200);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .booking-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        .card-header-custom {
            padding: 1.5rem 1.5rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Status Pills */
        .status-pill {
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .s-pending { background: #fff7ed; color: #c2410c; }
        .s-confirmed { background: #eef2ff; color: #4338ca; }
        .s-completed { background: #f0fdf4; color: #15803d; }
        .s-cancelled { background: #fef2f2; color: #b91c1c; }

        .service-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1.4;
            margin-bottom: 1rem;
        }

        /* Meta Grid */
        .meta-container {
            background: var(--bg-body);
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: var(--slate-700);
            font-weight: 600;
        }
        .meta-item i { color: var(--primary); font-size: 1.1rem; }

        .price-display {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--slate-900);
        }

        /* Action Buttons */
        .btn-stack { display: flex; flex-direction: column; gap: 10px; padding: 0 1.5rem 1.5rem; }
        .btn-modern {
            border-radius: 14px;
            padding: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.2s;
        }
        .btn-primary-soft {
            background: var(--primary-light);
            color: var(--primary);
            border: 1px solid transparent;
        }
        .btn-primary-soft:hover {
            background: var(--primary);
            color: white;
        }
        .btn-outline-cancel {
            background: white;
            color: #ef4444;
            border: 1px solid #fee2e2;
        }
        .btn-outline-cancel:hover {
            background: #fef2f2;
            border-color: #ef4444;
        }

        /* Hide Scrollbar for Pills */
        @media (max-width: 768px) {
            .nav-pills { overflow-x: auto; flex-wrap: nowrap; max-width: 100%; }
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['message'])): ?>
<div class="container mt-4">
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div><?= htmlspecialchars($_SESSION['message']) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php unset($_SESSION['message']); endif; ?>

<header class="page-header">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <a href="dashboard.php" class="text-decoration-none text-primary fw-bold small mb-2 d-inline-block">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
                <h1 class="fw-800 mb-1">My Bookings</h1>
                <p class="text-muted mb-0">Track and manage your service appointments</p>
            </div>
            <div class="mt-4 mt-md-0">
                <a href="../pages/categories.php" class="btn btn-primary btn-lg rounded-pill px-4 fw-800 shadow-primary">
                    <i class="bi bi-plus-lg me-2"></i>New Booking
                </a>
            </div>
        </div>
    </div>
</header>

<div class="container pb-5">
    <div class="mb-5 text-center text-md-start">
        <ul class="nav nav-pills shadow-sm">
            <li class="nav-item">
                <a class="nav-link <?= !$status_filter ? 'active' : '' ?>" href="orders.php">All</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter=='pending' ? 'active' : '' ?>" href="?status=pending">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter=='confirmed' ? 'active' : '' ?>" href="?status=confirmed">Confirmed</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter=='completed' ? 'active' : '' ?>" href="?status=completed">Completed</a>
            </li>
        </ul>
    </div>

    <?php if(!$bookings): ?>
        <div class="text-center py-5">
            <div class="bg-white d-inline-block p-4 rounded-circle shadow-sm mb-4">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
            </div>
            <h4 class="fw-800">No appointments found</h4>
            <p class="text-muted">You don't have any bookings in this category.</p>
            <a href="../pages/categories.php" class="btn btn-primary px-4 py-2 rounded-pill mt-2">Book Now</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($bookings as $b): ?>
            <div class="col-lg-4 col-md-6">
                <div class="booking-card h-100">
                    <?php if($b['booking_status'] == 'Cancelled'): ?>
                        <a href="orders.php?hide_id=<?= $b['id'] ?>" 
                           class="position-absolute top-0 end-0 m-3 text-secondary hover-danger"
                           onclick="return confirm('Remove this booking from page?')">
                            <i class="bi bi-trash3-fill"></i>
                        </a>
                    <?php endif; ?>

                    <div class="card-header-custom">
                        <span class="text-slate-400 fw-800 small text-uppercase">#BOK-<?= $b['id'] ?></span>
                        <div class="status-pill s-<?= strtolower($b['booking_status']) ?>">
                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i>
                            <?= $b['booking_status'] ?>
                        </div>
                    </div>

                    <div class="p-4 pt-0 flex-grow-1">
                        <h5 class="service-title"><?= htmlspecialchars($b['service_title']) ?></h5>

                        <div class="meta-container">
                            <div class="meta-item mb-2">
                                <i class="bi bi-calendar-week"></i>
                                <?= date('D, d M Y', strtotime($b['service_date'])) ?>
                            </div>
                            <div class="meta-item">
                                <i class="bi bi-clock-history"></i>
                                <?= htmlspecialchars($b['service_time']) ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-end pt-2">
                            <div>
                                <div class="text-slate-400 small fw-bold text-uppercase mb-1" style="font-size: 0.65rem;">Total Amount</div>
                                <div class="price-display">₹<?= number_format($b['service_price'], 2) ?></div>
                            </div>
                            <span class="badge rounded-pill px-3 py-2 <?= $b['payment_status']=='Paid' ? 'bg-success-subtle text-success':'bg-secondary-subtle text-secondary' ?>">
                                <i class="bi bi-shield-check me-1"></i> <?= $b['payment_status'] ?>
                            </span>
                        </div>
                    </div>

                    <div class="btn-stack">
                        <a href="view_details.php?id=<?= $b['id'] ?>" 
                           class="btn btn-primary-soft btn-modern">
                            View details
                        </a>

                        <?php if(strtolower($b['payment_status']) == 'pending'): ?>
                           <button class="btn btn-success btn-modern pay-btn"
        data-order-id="<?= $b['id'] ?>">
    <i class="bi bi-credit-card me-2"></i>Pay Now
</button>

                        <?php endif; ?>

                        <?php if($b['booking_status'] == 'Completed'): ?>
                            <a href="review.php?id=<?= $b['id'] ?>" 
                               class="btn btn-warning btn-modern text-dark">
                                <i class="bi bi-star-fill me-2"></i>Leave Review
                            </a>
                        <?php endif; ?>

                        <?php if(in_array($b['booking_status'], ['Pending','Confirmed'])): ?>
                            <a href="orders.php?cancel_id=<?= $b['id'] ?>" 
                               class="btn btn-outline-cancel btn-modern"
                                onclick="return confirm('Are you sure you want to cancel this booking?')">
                                Cancel Appointment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.querySelectorAll(".pay-btn").forEach(button => {

    button.addEventListener("click", function() {

        const orderId = this.getAttribute("data-order-id");

        fetch("create_payment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(res => res.json())
        .then(data => {

            if (data.status === "success") {

                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: "INR",
                    name: "ServiceHub",
                    description: "Service Payment",
                    order_id: data.razorpay_order_id,
                    handler: function(response) {
                        window.location.href = 
                            "verify_payment.php?payment_id=" 
                            + response.razorpay_payment_id 
                            + "&order_id=" + orderId;
                    },
                    theme: { color: "#4361ee" }
                };

                const rzp = new Razorpay(options);
                rzp.open();

            } else {
                alert(data.message);
            }
        });

    });

});
</script>

</body>
</html>