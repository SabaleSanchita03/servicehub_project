<?php
require 'config/db.php';

/* =========================
   FETCH ALL CATEGORIES FOR SEARCH DROPDOWN
========================= */
$allCategories = $pdo->query("
    SELECT * FROM categories 
    ORDER BY name ASC
")->fetchAll();

/* =========================
   HANDLE SEARCH FILTERS
========================= */
$category_id = $_GET['category'] ?? '';
$city       = $_GET['city'] ?? '';

$query = "
    SELECT 
        s.id,
        s.title AS service_name,
        s.image,
        s.price,
        s.duration,
        s.location AS city_name,       -- alias for PHP
        m.merchant_name,
        c.name AS category_name        -- alias for PHP
    FROM services s
    JOIN merchants m ON s.merchant_id = m.id
    JOIN categories c ON s.category_id = c.id
    WHERE 1
";

$params = [];

if (!empty($category_id)) {
    $query .= " AND s.category_id = :category_id";
    $params['category_id'] = $category_id;
}

if (!empty($city)) {
    $query .= " AND s.location LIKE :city";
    $params['city'] = "%$city%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | ServiceHub</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4338ca;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-800: #1e293b;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--slate-50); 
            color: var(--slate-800);
        }

        /* Navbar Styling */
        .navbar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--slate-200);
        }

        /* Logo Styling */
        .servicehub-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .logo-text {
            font-weight: 800; font-size: 1.4rem;
            letter-spacing: -0.04em; color: var(--slate-800);
        }
        .logo-text span { color: var(--primary); }

        /* Sidebar Filter Card */
        .filter-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--slate-200);
            padding: 24px;
            position: sticky;
            top: 100px;
        }

        /* Service Card Styling */
        .service-card {
            border: none;
            border-radius: 24px;
            background: white;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
            border: 1px solid transparent;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            border-color: var(--slate-200);
        }

        .img-wrapper {
            height: 220px;
            position: relative;
            overflow: hidden;
        }

        .img-wrapper img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.6s ease;
        }

        .service-card:hover img { transform: scale(1.08); }

        .price-tag {
            background: white;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 800;
            color: var(--slate-800);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .btn-view {
            background: var(--slate-800);
            color: white;
            border-radius: 14px;
            padding: 10px 20px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-view:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.05);
        }

        .empty-state {
            background: white;
            border-radius: 32px;
            padding: 60px;
            text-align: center;
            border: 2px dashed var(--slate-200);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a href="index.php" class="servicehub-logo">
            <span class="logo-icon"><i class="fas fa-layer-group"></i></span>
            <span class="logo-text">Service<span>Hub</span></span>
        </a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-light rounded-pill px-4 fw-600 border">
                <i class="bi bi-search me-2"></i>New Search
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-3 d-none d-lg-block">
            <div class="filter-card shadow-sm">
                <h5 class="fw-800 mb-4">Filters</h5>
                <form action="" method="GET">
                    <div class="mb-4">
                        <label class="form-label small fw-700 text-muted text-uppercase">Category</label>
                        <select name="category" class="form-select border-0 bg-light rounded-3">
                            <option value="">All Categories</option>
                            <?php foreach($allCategories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-700 text-muted text-uppercase">Location</label>
                        <input type="text" name="city" class="form-control border-0 bg-light rounded-3" 
                               placeholder="e.g. Mumbai" value="<?= htmlspecialchars($city) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-3 fw-700 py-2">Apply Filters</button>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-800 mb-1">Available Services</h2>
                    <p class="text-muted mb-0">Found <span class="text-dark fw-700"><?= count($services) ?></span> results</p>
                </div>
            </div>

            <?php if(count($services) === 0): ?>
                <div class="empty-state">
                    <div class="mb-4 text-muted opacity-20">
                        <i class="bi bi-search" style="font-size: 80px;"></i>
                    </div>
                    <h3 class="fw-800">No Experts Found</h3>
                    <p class="text-muted">We couldn't find any services matching your current filters.</p>
                    <a href="index.php" class="btn btn-primary rounded-pill px-5 py-2 fw-700 mt-3">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($services as $s): 
                        $imagePath = 'assets/services/' . $s['image'];
                        $displayImage = (!empty($s['image']) && file_exists($imagePath))
                            ? $imagePath
                            : "https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80";
                    ?>
                    <div class="col-md-6">
                        <div class="card service-card h-100 shadow-sm">
                            <div class="img-wrapper">
                                <img src="<?= $displayImage ?>" alt="<?= htmlspecialchars($s['service_name']) ?>">
                                <div class="position-absolute top-0 end-0 m-3">
                                    <div class="price-tag">₹<?= number_format($s['price'], 0) ?></div>
                                </div>
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-700 small shadow-sm">
                                        <?= htmlspecialchars($s['category_name']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-body p-4">
                                <h5 class="fw-800 text-dark mb-1"><?= htmlspecialchars($s['service_name']) ?></h5>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="bi bi-patch-check-fill text-primary"></i>
                                    <span class="text-muted small fw-600"><?= htmlspecialchars($s['merchant_name']) ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <div class="text-secondary small fw-700">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <?= htmlspecialchars($s['city_name']) ?>
                                    </div>
                                    <a href="./pages/service_details.php?id=<?= $s['id'] ?>" class="btn btn-view">
                                        View Details<i class="bi bi-arrow-right-short ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>