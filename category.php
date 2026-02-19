<?php
require 'config/db.php';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$category_id = (int) $_GET['id'];

// Fetch category info
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if(!$category) {
    die("Category not found.");
}

/** * UPDATED SQL: 
 * 1. We select s.title explicitly to avoid confusion.
 * 2. We select m.business_name for the merchant name.
 * 3. We use m.rating and ensure it's selected properly.
 */
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.title AS service_name,
        s.image,
        s.price,
        s.duration,
        s.location,
        u.name AS merchant_name
    FROM services s
    JOIN merchants m ON s.merchant_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE s.category_id = ?
    ORDER BY s.id DESC
");
$stmt->execute([$category_id]);
$services = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']) ?> Services | ServiceHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; color: #1e293b; }

        /* ===== ServiceHub Logo ===== */
        .servicehub-logo { display: inline-flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-icon {
            width: 44px; height: 44px; border-radius: 14px;
            background: linear-gradient(135deg, #6366f1, #4338ca);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.3rem; box-shadow: 0 6px 18px rgba(99, 102, 241, 0.35);
        }
        .logo-text { font-weight: 800; font-size: 1.6rem; letter-spacing: -0.04em; color: #1e293b; }
        .logo-text span { color: #6366f1; }

        /* --- Service Card Styles --- */
        .service-card { 
            border: none; border-radius: 24px; overflow: hidden; 
            background: #fff; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .service-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important; }
        .service-img { height: 210px; object-fit: cover; width: 100%; }
        
        .badge-rating { 
            background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); 
            color: #f08c00; padding: 6px 12px; border-radius: 12px; 
            font-weight: 700; font-size: 0.85rem; box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .location-text { font-size: 0.85rem; color: #64748b; font-weight: 500; }
        .business-name { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: #6366f1; letter-spacing: 0.05em; }
        
        .btn-book { 
            border-radius: 14px; font-weight: 700; padding: 10px 20px;
            background: #6366f1; border: none; transition: 0.3s;
        }
        .btn-book:hover { background: #4338ca; transform: scale(1.02); }

        .section-title { font-weight: 800; letter-spacing: -0.02em; }
    </style>
</head>
<body>

<nav class="navbar bg-white border-bottom sticky-top py-3">
    <div class="container">
        <a href="index.php" class="servicehub-logo">
            <span class="logo-icon"><i class="fas fa-layer-group"></i></span>
            <span class="logo-text">Service<span>Hub</span></span>
        </a>
    </div>
</nav>

<div class="container py-5">
    <div class="mb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted small fw-600">Home</a></li>
                <li class="breadcrumb-item active small fw-600" aria-current="page"><?= htmlspecialchars($category['name']) ?></li>
            </ol>
        </nav>
        <h2 class="section-title mb-1"><?= htmlspecialchars($category['name']) ?> Services</h2>
        <p class="text-secondary">Discover the best experts for your needs</p>
    </div>

    <?php if(count($services) === 0): ?>
        <div class="text-center py-5">
            <div class="mb-3 opacity-25"><i class="bi bi-search" style="font-size: 4rem;"></i></div>
            <h4 class="fw-700 text-muted">No services found</h4>
            <p class="text-secondary">We couldn't find any services in this category at the moment.</p>
            <a href="index.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Go Back Home</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
           <?php foreach($services as $s): ?>
<div class="col-md-6 col-lg-4">
    <div class="card service-card h-100 shadow-sm">

        <img src="assets/services/<?= $s['image'] ?: 'default.png' ?>" 
             class="service-img" 
             alt="<?= htmlspecialchars($s['service_name']) ?>">

        <div class="card-body p-4">

            <!-- MERCHANT NAME -->
            <div class="mb-1">
                <span class="fw-bold text-dark">
                    <?= htmlspecialchars($s['merchant_name']) ?>
                </span>
            </div>

            <!-- SERVICE NAME -->
            <h5 class="fw-800 mb-2">
                <?= htmlspecialchars($s['service_name']) ?>
            </h5>

            <!-- LOCATION -->
            <div class="text-muted small mb-3">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                <?= htmlspecialchars($s['location'] ?: 'Location N/A') ?>
            </div>

            <!-- PRICE -->
            <div class="d-flex justify-content-between align-items-center">
                <span class="fs-5 fw-bold">₹<?= number_format($s['price'],0) ?></span>
                <a href="./pages/service_details.php?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">
                    Book Now
                </a>
            </div>

        </div>
    </div>
</div>
<?php endforeach; ?>

        </div>
    <?php endif; ?>
</div>

<footer class="bg-white border-top py-4 text-center mt-5">
    <p class="text-muted small mb-0">© <?= date('Y') ?> ServiceHub. All rights reserved.</p>
</footer>

</body>
</html>