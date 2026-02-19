<?php
require 'config/db.php';

if (!isset($_GET['id'])) {
    die("Merchant not found");
}

$merchant_id = (int) $_GET['id'];

/* === Fetch Merchant (PERSON) Info === */
$stmt = $pdo->prepare("
    SELECT 
        m.id,
        u.name AS merchant_name,
        m.city,
        m.rating
    FROM merchants m
    JOIN users u ON m.user_id = u.id
    WHERE m.id = ?
");
$stmt->execute([$merchant_id]);
$merchant = $stmt->fetch();

if (!$merchant) {
    die("Merchant not found");
}

/* === Fetch Merchant Services === */
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.title,
        s.price,
        s.image,
        s.location,
        s.duration,
        c.name AS category
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.merchant_id = ?
    ORDER BY s.id DESC
");
$stmt->execute([$merchant_id]);
$services = $stmt->fetchAll();
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($merchant['merchant_name']) ?> | ServiceHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <!-- Merchant Header -->
    <div class="bg-white rounded-4 p-4 shadow-sm mb-5">
        <h2 class="fw-bold mb-1"><?= htmlspecialchars($merchant['merchant_name']) ?></h2>

        <div class="text-muted mb-2">
            <i class="bi bi-geo-alt-fill text-danger"></i>
            <?= htmlspecialchars($merchant['city'] ?: 'Location not specified') ?>
        </div>

        <span class="badge bg-warning text-dark">
            ⭐ <?= number_format($merchant['rating'],1) ?>
        </span>
    </div>

    <!-- Services -->
    <h4 class="fw-bold mb-4">Services</h4>

    <div class="row g-4">
        <?php foreach($services as $s): ?>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <img src="assets/services/<?= $s['image'] ?: 'default.png' ?>" 
                     class="card-img-top" style="height:200px;object-fit:cover;">

                <div class="card-body">
                    <span class="text-primary small fw-bold"><?= htmlspecialchars($s['category']) ?></span>
                    <h5 class="fw-bold mt-1"><?= htmlspecialchars($s['title']) ?></h5>

                    <p class="text-muted small mb-2">
                        📍 <?= htmlspecialchars($s['location'] ?: $merchant['city']) ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">₹<?= number_format($s['price'],0) ?></span>
                        <a href="book_service.php?id=<?= $s['id'] ?>" 
                           class="btn btn-primary btn-sm">
                            Book
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>
