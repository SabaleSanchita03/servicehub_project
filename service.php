<?php
require "config/db.php";

$service_id = $_GET['id'] ?? null;
if (!$service_id) {
    die("Service not found");
}

/* Fetch service + merchant + category */
$stmt = $pdo->prepare("
    SELECT 
        s.*,
        m.business_name,
        m.city,
        m.rating,
        c.name AS category_name
    FROM services s
    JOIN merchants m ON s.merchant_id = m.id
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found");
}

$imagePath = "assets/services/" . ($service['image'] ?? 'default.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> – ServiceHub</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #6366f1;
            --brand-dark: #0f172a;
            --bg-body: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg-body); color: var(--brand-dark); }

        /* Hero Image Modernization */
        .service-img-wrapper {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .service-img-wrapper img {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .service-img-wrapper:hover img { transform: scale(1.02); }

        /* Sidebar Booking Card */
        .booking-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .price-text {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--brand-primary);
            letter-spacing: -1px;
        }

        .btn-book {
            background: var(--brand-primary);
            border: none;
            border-radius: 14px;
            padding: 0.8rem;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-book:hover {
            background: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }

        .info-badge {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 10px;
        }
        
        .breadcrumb-item a { text-decoration: none; color: #64748b; font-weight: 500; }
    </style>
</head>

<body>
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="#"><?= htmlspecialchars($service['category_name']) ?></a></li>
            <li class="breadcrumb-item active">Service Details</li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-7">
            <div class="service-img-wrapper mb-4">
                <img src="<?= $imagePath ?>" alt="Service Image">
            </div>

            <div class="d-flex gap-2 mb-3">
                <span class="info-badge"><i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($service['category_name']) ?></span>
                <span class="info-badge"><i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($service['city']) ?></span>
            </div>

            <h1 class="fw-800 mb-3" style="font-weight: 800; letter-spacing: -1px;"><?= htmlspecialchars($service['title']) ?></h1>
            
            <div class="mb-5">
                <h5 class="fw-bold mb-3">Description</h5>
                <p class="text-muted leading-relaxed" style="line-height: 1.7;">
                    <?= nl2br(htmlspecialchars($service['description'])) ?>
                </p>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 border rounded-4 bg-white d-flex align-items-center">
                        <i class="bi bi-shield-check text-success fs-3 me-3"></i>
                        <div>
                            <span class="d-block fw-bold small">Verified Provider</span>
                            <span class="text-muted small">Quality Guaranteed</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded-4 bg-white d-flex align-items-center">
                        <i class="bi bi-clock-history text-primary fs-3 me-3"></i>
                        <div>
                            <span class="d-block fw-bold small">Duration</span>
                            <span class="text-muted small"><?= htmlspecialchars($service['duration'] ?? 'Standard') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="sticky-top" style="top: 20px;">
                <div class="booking-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="price-text">₹<?= number_format($service['price'], 2) ?></div>
                        <div class="text-warning">
                            <i class="bi bi-star-fill"></i> 
                            <span class="fw-bold text-dark"><?= number_format($service['rating'], 1) ?></span>
                        </div>
                    </div>

                    <div class="p-3 rounded-4 bg-light mb-4">
                        <div class="small text-muted mb-1">Service Provider</div>
                        <div class="fw-bold"><i class="bi bi-shop me-2 text-primary"></i><?= htmlspecialchars($service['business_name']) ?></div>
                    </div>

                    <a href="booking.php?service_id=<?= $service['id'] ?>" class="btn btn-primary btn-book w-100 mb-3 text-white">
                        Book Now
                    </a>
                    
                    <div class="text-center">
                        <span class="small text-muted"><i class="bi bi-lightning-charge-fill text-warning"></i> Instant confirmation</span>
                    </div>

                    <hr class="my-4">
                    
                    <div class="small text-muted">
                        <p class="mb-2"><i class="bi bi-check2-circle me-2 text-success"></i> No hidden fees</p>
                        <p class="mb-0"><i class="bi bi-check2-circle me-2 text-success"></i> Verified by ServiceHub</p>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <p class="small text-muted">Need help? <a href="#" class="text-primary fw-bold text-decoration-none">Contact Merchant</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>