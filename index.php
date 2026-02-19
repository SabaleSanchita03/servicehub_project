<?php
session_start();
require 'config/db.php';


/* =========================
   FETCH ALL CATEGORIES FOR SEARCH
========================= */
$allCategories = $pdo->query("
    SELECT * FROM categories 
    ORDER BY name ASC
")->fetchAll();

/* =========================
   FETCH 6 CATEGORIES FOR HOMEPAGE BROWSE SECTION
========================= */
$homepageCategories = $pdo->query("
    SELECT * FROM categories 
    ORDER BY id DESC 
    LIMIT 6
")->fetchAll();

/* =========================
   FETCH TOP RATED SERVICES
========================= */
$serviceStmt = $pdo->prepare("
    SELECT 
        s.id,
        s.title AS service_name,
        s.image,
        s.price,
        s.duration,
        s.location,
        u.name AS merchant_name,
        c.name AS category
    FROM services s
    JOIN merchants m ON s.merchant_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN categories c ON s.category_id = c.id
    ORDER BY s.id DESC
    LIMIT 6
");
$serviceStmt->execute();
$services = $serviceStmt->fetchAll();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceHub | Premium Home Services</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4361ee, #4cc9f0);
            --surface-color: #ffffff;
            --bg-light: #f8f9fa;
            --text-dark: #2b2d42;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--text-dark);
            background-color: #fff;
        }
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


        .navbar {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        .navbar-brand { color: #4361ee !important; letter-spacing: -0.5px; }

        .hero { 
            background: var(--primary-gradient);
            color: white; 
            padding: 120px 0 100px; 
            border-radius: 0 0 50px 50px;
        }

        .search-container {
            background: white;
            padding: 15px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-top: -50px;
        }

        .category-card {
            border: none;
            border-radius: 24px;
            background: #f8f9fa;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        .category-card:hover {
            background: #fff;
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }
        .category-icon-wrapper {
            height: 70px; width: 70px;
            background: white;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto; border-radius: 20px;
            box-shadow: 0 8px 15px rgba(0,0,0,0.05);
            color: #4361ee;
        }

        .merchant-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: 0.3s;
            background: #fff;
        }
        .merchant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }
        .merchant-img-container {
            height: 220px;
            overflow: hidden;
            background: #eee;
        }
        .merchant-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .badge-rating {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            color: #f08c00;
            padding: 5px 12px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-modern { padding: 12px 28px; border-radius: 14px; font-weight: 600; }
        .btn-primary-modern { background: #4361ee; border: none; color: white; }
        .fw-800 { font-weight: 800; }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a href="index.php" class="servicehub-logo">
            <span class="logo-icon">
                <i class="fas fa-layer-group"></i>
            </span>
            <span class="logo-text">
                Service<span>Hub</span>
            </span>
        </a>

<div class="ms-auto d-flex align-items-center gap-3">
<?php if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])): ?>
    <?php 
        $role = $_SESSION['role'];
        switch($role){
            case 'merchant':
                $dashboardPath = 'merchant/dashboard.php';
                break;
            case 'admin':
                $dashboardPath = 'admin/dashboard.php';
                break;
            default:
                $dashboardPath = 'customer/dashboard.php';
        }
    ?>
    <span class="text-muted small d-none d-md-block">
        Welcome, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>
    </span>

    <a href="<?= $dashboardPath ?>" class="btn btn-primary-modern btn-modern shadow-sm">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
    </a>

    <a href="auth/logout.php" class="btn btn-link text-decoration-none text-danger fw-600 px-0 ms-2">
        Logout
    </a>

<?php else: ?>
    <a href="auth/login.php" class="btn btn-link text-decoration-none text-dark fw-600">Login</a>
    <a href="auth/register.php" class="btn btn-primary-modern btn-modern shadow-sm">Get Started</a>
<?php endif; ?>
</div>
    </div>
</nav>

<section class="hero text-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill fw-600">#1 Trusted Service Marketplace</span>
                <h1 class="display-4 fw-800 mb-3">Expert services, <br>delivered at your doorstep.</h1>
            </div>
        </div>
    </div>
</section>

<div class="container mb-5">
    <div class="search-container mx-auto col-lg-10">
        <form action="search.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-grid"></i></span>
                    <select name="category" class="form-select border-start-0">
    <option value="">What service do you need?</option>
    <?php foreach($allCategories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
    <?php endforeach; ?>
</select>

                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" name="city" class="form-control border-start-0" placeholder="Your City">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100 btn-modern">Search Experts</button>
            </div>
        </form>
    </div>
</div>

<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h3 class="fw-800 mb-1">Browse Categories</h3>
                <p class="text-muted small mb-0">Find the right expert for your needs</p>
            </div>
            <a href="./pages/categories.php" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-600">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach($homepageCategories as $cat): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="category.php?id=<?= $cat['id'] ?>" class="text-decoration-none group">
                    <div class="card category-card border-0 text-center p-4 transition-all">
                        <div class="category-icon-wrapper mb-3 mx-auto shadow-sm">
                            <i class="bi <?= !empty($cat['icon']) ? htmlspecialchars($cat['icon']) : 'bi-grid' ?> fs-2"></i>
                        </div>
                        <h6 class="fw-700 text-dark mb-0 small text-truncate">
                            <?= htmlspecialchars($cat['name']) ?>
                        </h6>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
    /* Premium Styling for Category Cards */
    .category-card {
        background: #f8fafc;
        border-radius: 24px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .category-icon-wrapper {
        width: 64px;
        height: 64px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        color: #4361ee;
        transition: 0.3s;
    }

    /* Hover Effects */
    .category-card:hover {
        background: #ffffff;
        transform: translateY(-10px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    }

    .category-card:hover .category-icon-wrapper {
        background: #4361ee;
        color: #ffffff;
        transform: scale(1.1);
    }

    .fw-800 { font-weight: 800; }
    .fw-700 { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .transition-all { transition: all 0.3s ease; }
</style>
<section class="bg-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h6 class="text-primary fw-bold text-uppercase tracking-wider">Our Offerings</h6>
                <h2 class="fw-800 mb-0">Top Rated Services</h2>
            </div>
            <a href="all-services.php" class="btn btn-link text-decoration-none fw-600">View All <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($services as $s): 
                $imagePath = 'assets/services/' . $s['image'];
                $displayImage = (!empty($s['image']) && file_exists($imagePath))
                    ? $imagePath
                    : "https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80";
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card service-card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="position-relative overflow-hidden" style="height: 220px;">
                        <img src="<?= $displayImage ?>" 
                             class="card-img-top h-100 w-100 object-fit-cover transition-zoom" 
                             alt="<?= htmlspecialchars($s['service_name']) ?>">
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-white text-dark shadow-sm py-2 px-3">
                                <i class="bi bi-star-fill text-warning me-1"></i> 4.9
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-2">
                            <span class="text-primary fw-700 small text-uppercase ls-1">
                                <?= htmlspecialchars($s['category']) ?>
                            </span>
                        </div>

                        <h5 class="fw-800 mb-1">
                            <a href="service_details.php?id=<?= $s['id'] ?>" class="text-dark text-decoration-none stretched-link">
                                <?= htmlspecialchars($s['service_name']) ?>
                            </a>
                        </h5>
                        
                        <p class="text-muted small mb-3">
                            by <span class="fw-600 text-dark"><?= htmlspecialchars($s['merchant_name']) ?></span>
                        </p>

                        <div class="d-flex align-items-center gap-2 mb-4 mt-auto">
                            <div class="text-secondary small">
                                <i class="bi bi-geo-alt text-danger me-1"></i>
                                <?= htmlspecialchars($s['location'] ?? 'Remote / Global') ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div>
                                <span class="text-muted small d-block">Starting from</span>
                                <span class="fs-5 fw-800 text-dark">₹<?= number_format($s['price'], 0) ?></span>
                            </div>
                            <div class="position-relative" style="z-index: 2;">
                                <a href="./pages/service_details.php?id=<?= $s['id'] ?>"class="btn btn-primary rounded-pill px-4 fw-600 shadow-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<footer class="bg-white border-top py-5 text-center">
    <div class="container">
        <h4 class="fw-800 text-primary mb-3">ServiceHub</h4>
        <p class="text-muted small mb-0">© <?= date('Y') ?> ServiceHub. Proudly made in India 🇮🇳</p>
    </div>
</footer>
            </body>
            </html>


         