<?php
session_start(); 
require "../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit;
}

$service_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT s.*, 
           m.merchant_name, 
           m.id AS merchant_id, 
           c.name AS category
    FROM services s
    JOIN merchants m ON s.merchant_id = m.id
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> – ServiceHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --bg-subtle: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        }

        body { 
            background-color: var(--bg-subtle); 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Hero Image Styling */
        .service-banner-container {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            background: #e2e8f0;
            margin-bottom: 2.5rem;
        }

        .service-banner { 
            width: 100%; 
            height: 500px; 
            object-fit: cover; 
            transition: transform 0.5s ease;
        }

        .service-banner-container:hover .service-banner {
            transform: scale(1.02);
        }

        /* Typography & Badges */
        .badge-category { 
            background: rgba(67, 97, 238, 0.1); 
            color: var(--primary-color); 
            font-weight: 600; 
            font-size: 0.75rem; 
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 16px;
        }

        .price-display { 
            font-size: 2.5rem; 
            font-weight: 800; 
            color: var(--text-main); 
            letter-spacing: -0.03em; 
        }

        /* Sidebar Card */
        .sticky-card { position: sticky; top: 2rem; }
        
        .booking-card {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            background: #ffffff;
        }

        /* Merchant Section */
        .merchant-box { 
            border: 1px solid #f1f5f9; 
            border-radius: 20px; 
            padding: 24px; 
            background: #fff; 
            transition: all 0.2s ease; 
        }
        
        .merchant-box:hover { 
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.05);
        }

        .avatar-placeholder {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, var(--primary-color), #7209b7);
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* Buttons */
        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 14px;
            font-weight: 700;
            transition: all 0.2s;
        }

        .btn-primary-custom:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(67, 97, 238, 0.3);
        }

        .btn-back {
            background: white;
            border: 1px solid #e2e8f0;
            color: var(--text-muted);
            font-weight: 500;
            transition: 0.2s;
        }

        .btn-back:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        .description-text {
            line-height: 1.8;
            color: #475569;
            font-size: 1.05rem;
        }

    </style>
</head>
<body>

<div class="container py-5">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <a href="javascript:history.back()" class="btn btn-back rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Back to Explorer
        </a>
        <div class="d-flex gap-2">
            <button class="btn btn-back rounded-circle p-2" style="width: 40px; height: 40px;"><i class="bi bi-share"></i></button>
            <button class="btn btn-back rounded-circle p-2" style="width: 40px; height: 40px;"><i class="bi bi-heart"></i></button>
        </div>
    </div>

    <div class="row g-lg-5">
        <div class="col-lg-8">
            <div class="service-banner-container">
                <?php 
                    $imgFilename = $service['image'];
                    $imgPath = "../assets/services/" . $imgFilename;
                    $displayImg = (!empty($imgFilename) && file_exists($imgPath)) 
                                  ? $imgPath 
                                  : "https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=1200&q=80";
                ?>
                <img src="<?= $displayImg ?>" class="service-banner" alt="<?= htmlspecialchars($service['title']) ?>">
            </div>

            <div class="content-body px-1">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge badge-category rounded-pill">
                        <?= htmlspecialchars($service['category']) ?>
                    </span>
                    <span class="text-muted small">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= htmlspecialchars($service['location'] ?? 'Remote/Global') ?>
                    </span>
                </div>

                <h1 class="fw-bold mb-4" style="color: #0f172a; font-size: 3rem; letter-spacing: -0.02em;">
                    <?= htmlspecialchars($service['title']) ?>
                </h1>

                <hr class="my-5" style="opacity: 0.08;">

                <div class="mb-5">
                    <h4 class="fw-bold mb-3 text-dark">Service Description</h4>
                    <div class="description-text">
                        <?= nl2br(htmlspecialchars($service['description'])) ?>
                    </div>
                </div>

                <div class="merchant-box d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($service['merchant_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Offered by</p>
                            <h6 class="mb-0 fw-bold">
                                <?= htmlspecialchars($service['merchant_name']) ?>
                                <i class="bi bi-patch-check-fill text-primary ms-1" title="Verified Merchant"></i>
                            </h6>
                        </div>
                    </div>
                    <a href="merchant.php?id=<?= $service['merchant_id'] ?>" 
                       class="btn btn-light rounded-pill px-4 fw-semibold border">
                        View Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-card">
                <div class="card booking-card border-0">
                    <div class="p-4">
                        <div class="mb-4">
                            <span class="text-muted d-block small fw-bold text-uppercase mb-1">Total Investment</span>
                            <div class="price-display">
                                ₹<?= number_format($service['price'], 2) ?>
                            </div>
                        </div>

                        <div class="features-list mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <i class="bi bi-clock-history text-primary"></i>
                                </div>
                                <span class="small fw-medium">Delivery: <strong><?= htmlspecialchars($service['duration']) ?></strong></span>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <i class="bi bi-shield-check text-success"></i>
                                </div>
                                <span class="small fw-medium">Full Service Guarantee</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <i class="bi bi-chat-dots text-info"></i>
                                </div>
                                <span class="small fw-medium">Free Consultation Included</span>
                            </div>
                        </div>

                        <button id="bookBtn" class="btn btn-primary-custom w-100 mb-3">
    Proceed to Booking
</button>


                        <p class="text-center text-muted mb-0" style="font-size: 0.75rem;">
                            <i class="bi bi-lock me-1"></i> SSL Encrypted Payment
                        </p>
                    </div>

                    <div class="bg-light p-3 text-center rounded-bottom-4 border-top">
                        <span class="small text-secondary fw-medium">
                            Avg. response: <span class="text-dark fw-bold">2 hours</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('bookBtn').addEventListener('click', function() {
    <?php if (!isset($_SESSION['user_id'])): ?>
        if (confirm("You need to login first to book this service. Proceed to login?")) {
            window.location.href = "/servicehub/auth/login.php?redirect=/servicehub/pages/service_details.php?id=<?= $service['id'] ?>";
        }
    <?php else: ?>
        window.location.href = "/servicehub/pages/book_service.php?id=<?= $service['id'] ?>";
    <?php endif; ?>
});


</script>


</body>
</html>