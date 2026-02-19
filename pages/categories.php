<?php
require "../config/db.php";

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Explore Services by Category – ServiceHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        /* Category Hero Section */
        .category-header {
            background: var(--primary-gradient);
            padding: 80px 0;
            color: white;
            border-radius: 0 0 40px 40px;
            margin-bottom: 50px;
        }

        /* Category Card Styles */
        .category-card {
            border: none;
            border-radius: 24px;
            background: #ffffff;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .category-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(67, 97, 238, 0.15) !important;
        }

        .category-card:hover::before {
            opacity: 0.03; /* Very subtle tint on hover */
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: #f1f4ff;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.3s ease;
        }

        .category-card:hover .icon-box {
            background: #4361ee;
            transform: rotate(-5deg) scale(1.1);
        }

        .category-card:hover .bi {
            color: #ffffff !important;
        }

        .category-title {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.15rem;
            letter-spacing: -0.02em;
        }

        .explore-link {
            font-size: 0.85rem;
            font-weight: 600;
            color: #4361ee;
            opacity: 0;
            transition: all 0.3s ease;
            transform: translateY(10px);
        }

        .category-card:hover .explore-link {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<header class="category-header shadow-sm">
    <div class="container text-center">
        <a href="javascript:history.back()" class="btn btn-back rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i> Back to Explorer
        </a>
        <h1 class="display-5 fw-bold mb-3">What are you looking for?</h1>
        <p class="lead opacity-75">Select a category to discover top-rated professionals near you.</p>
    </div>
</header>

<div class="container pb-5">
    <div class="row g-4 justify-content-center">
        <?php foreach($categories as $cat): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a href="services.php?category=<?= $cat['id'] ?>" class="text-decoration-none">
                <div class="card category-card shadow-sm p-4 text-center h-100">
                    <div class="icon-box">
                        <i class="bi <?= htmlspecialchars($cat['icon']) ?> fs-1 text-primary"></i>
                    </div>
                    <h5 class="category-title mb-2"><?= htmlspecialchars($cat['name']) ?></h5>
                    <div class="explore-link">
                        Browse Services <i class="bi bi-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<footer class="text-center py-5 text-muted small">
    <div class="container">
        <hr class="mb-4 opacity-25">
        <p>&copy; 2026 ServiceHub Marketplace. All Categories Verified.</p>
    </div>
</footer>

</body>
</html>