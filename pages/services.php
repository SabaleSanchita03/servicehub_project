<?php
require "../config/db.php";

if (!isset($_GET['category'])) {
    header("Location: categories.php");
    exit;
}

$category_id = $_GET['category'];

// Get category name
$stmt_cat = $pdo->prepare("SELECT name FROM categories WHERE id=?");
$stmt_cat->execute([$category_id]);
$category = $stmt_cat->fetch();

// Fetch services under this category
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.title AS service_name,
        s.image,
        s.price,
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
    <title><?= htmlspecialchars($category['name']) ?> Services – ServiceHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .fw-800 { font-weight: 800; }
        
        /* Service Image Styling */
        .service-img-container {
            height: 200px;
            overflow: hidden;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        
        .service-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .service-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }

        .service-card:hover .service-img {
            transform: scale(1.05);
        }

        .merchant-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="categories.php" class="text-decoration-none">Categories</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($category['name']) ?></li>
        </ol>
    </nav>

    <h2 class="fw-800 mb-4"><?= htmlspecialchars($category['name']) ?> Services</h2>

    <?php if(!$services): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="bi bi-search display-1 text-muted"></i>
            <h4 class="mt-3">No services found</h4>
            <p class="text-muted">Be the first merchant to offer <?= htmlspecialchars($category['name']) ?> here!</p>
        </div>
    <?php else: ?>

    <div class="row g-4">
        <?php foreach($services as $s): 
            // Fix: Pointing to the correct assets folder path
            $imgFilename = $s['image'];
            $imgPath = "../assets/services/" . $imgFilename;
            
            // Check if file exists, else use placeholder
            $displayImg = (!empty($imgFilename) && file_exists($imgPath)) 
                          ? $imgPath 
                          : "https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80";
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card service-card h-100 shadow-sm">
                <div class="service-img-container">
                    <img src="<?= $displayImg ?>" 
                         class="service-img" 
                         alt="<?= htmlspecialchars($s['service_name']) ?>">
                </div>

                <div class="card-body p-4">
                    <div class="merchant-label mb-1">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($s['merchant_name']) ?>
                    </div>

                    <h5 class="fw-800 mb-2">
                        <?= htmlspecialchars($s['service_name']) ?>
                    </h5>

                    <div class="text-muted small mb-4">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        <?= htmlspecialchars($s['location'] ?: 'Remote / Location N/A') ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <div class="d-flex flex-column">
                            <span class="text-muted x-small" style="font-size: 0.7rem;">Starts at</span>
                            <span class="fs-5 fw-bold text-dark">₹<?= number_format($s['price'],0) ?></span>
                        </div>
                        <a href="service_details.php?id=<?= $s['id'] ?>" class="btn btn-primary rounded-pill px-4">
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

</body>
</html>