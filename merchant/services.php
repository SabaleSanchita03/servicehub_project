<?php
require "../includes/auth_check.php";
require "../config/db.php";

$user_id = $_SESSION['user_id'];

// Get merchant_id
$stmt = $pdo->prepare("SELECT id FROM merchants WHERE user_id=?");
$stmt->execute([$user_id]);
$merchant_id = $stmt->fetchColumn();

// Fetch services - Added 'location' to the logic (ensure this column exists in your 'services' table)
$stmt = $pdo->prepare("SELECT * FROM services WHERE merchant_id=? ORDER BY created_at DESC");
$stmt->execute([$merchant_id]);
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Services – ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --brand-color: #6366f1;
            --brand-hover: #4f46e5;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
        }

        h2, h3, .service-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .container {
            max-width: 1200px; /* Slightly wider to accommodate new column */
        }

        .page-header {
            background: #fff;
            padding: 2rem;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 2.5rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .card-main {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table thead th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody td {
            padding: 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .service-img-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            overflow: hidden;
            background: #f1f5f9;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
        }

        .service-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .service-title {
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .location-badge {
            display: inline-flex;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .price-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            color: var(--brand-color);
            font-size: 1rem;
        }

        .duration-pill {
            display: inline-flex;
            align-items: center;
            background-color: #f0fdf4;
            color: #16a34a;
            padding: 0.35rem 0.7rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--brand-color) 0%, #4338ca 100%);
            border: none;
            border-radius: 12px;
            padding: 0.7rem 1.4rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .action-link {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            text-decoration: none;
        }

        .edit-link { background-color: #eef2ff; color: var(--brand-color); }
        .delete-link { background-color: #fff1f2; color: #ef4444; }
    </style>
</head>

<body>
<div class="container py-5">

    <div class="page-header d-md-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">My Services</h2>
            <p class="text-muted small mb-0">Managing <strong><?= count($services) ?></strong> services in your location.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="service_add.php" class="btn btn-add">
                <i class="bi bi-plus-lg me-2"></i> Add New Service
            </a>
        </div>
    </div>

    <div class="card-main">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 35%;">Service</th>
                        <th style="width: 20%;">Location</th>
                        <th style="width: 15%;">Price</th>
                        <th style="width: 15%;">Duration</th>
                        <th style="width: 15%; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($services) == 0): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">No services found. <a href="service_add.php">Add one now</a>.</div>
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php foreach($services as $s): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="service-img-wrapper me-3">
                                    <img src="../assets/services/<?= $s['image'] ?: 'default.png' ?>" class="service-img" alt="">
                                </div>
                                <div>
                                    <div class="service-title"><?= htmlspecialchars($s['title']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 200px;"><?= htmlspecialchars($s['description']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="location-badge">
                                <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
                                <?= htmlspecialchars($s['location'] ?: 'Remote / Online') ?>
                            </div>
                        </td>
                        <td>
                            <span class="price-text">₹<?= number_format($s['price'], 2) ?></span>
                        </td>
                        <td>
                            <span class="duration-pill">
                                <i class="bi bi-clock me-1"></i> <?= $s['duration'] ?: 'N/A' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="service_edit.php?id=<?= $s['id'] ?>" class="action-link edit-link"><i class="bi bi-pencil-square"></i></a>
                                <a href="service_delete.php?id=<?= $s['id'] ?>" class="action-link delete-link" onclick="return confirm('Delete this service?')"><i class="bi bi-trash3"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="dashboard.php" class="text-decoration-none text-muted small fw-medium">
            <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Back to Dashboard
        </a>
    </div>

</div>
</body>
</html>
