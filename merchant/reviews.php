<?php
require "../includes/auth_check.php";
require "../config/db.php";

// Only merchants allowed
if ($_SESSION['role'] !== 'merchant') {
    header("Location: ../auth/login.php");
    exit;
}

// Get merchant id
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM merchants WHERE user_id = ?");
$stmt->execute([$user_id]);
$merchant = $stmt->fetch();

if (!$merchant) {
    die("Merchant not found.");
}
$merchant_id = $merchant['id'];

// Fetch reviews for merchant's services
$stmt_reviews = $pdo->prepare("
    SELECT r.id, r.rating, r.comment, r.created_at, 
           u.name AS customer_name, s.title AS service_title
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    JOIN users u ON r.user_id = u.id
    WHERE s.merchant_id = ?
    ORDER BY r.created_at DESC
");
$stmt_reviews->execute([$merchant_id]);
$reviews = $stmt_reviews->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Reviews – ServiceHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --bg-body: #f8fafc;
            --star-color: #f59e0b;
            --star-empty: #e2e8f0;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-body); 
            color: #1e293b; 
        }

        /* Review Card Enhancements */
        .review-card { 
            background: #fff; 
            border-radius: 20px; 
            padding: 2rem; 
            margin-bottom: 1.5rem; 
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
            transition: transform 0.2s;
        }

        .review-card:hover {
            transform: translateY(-2px);
        }

        /* Avatar Placeholder */
        .avatar-circle {
            width: 48px;
            height: 48px;
            background: #f1f5f9;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .star-fill { color: var(--star-color); }
        .star-empty { color: var(--star-empty); }

        .customer-name { 
            font-weight: 700; 
            font-size: 1.05rem;
            color: #0f172a;
        }

        .service-tag { 
            background: #eff6ff;
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 100px;
            display: inline-block;
        }

        .review-date { 
            font-size: 0.85rem; 
            color: #94a3b8; 
        }

        .comment-box {
            font-size: 1rem;
            line-height: 1.6;
            color: #475569;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<div class="container py-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h3 class="fw-bold mb-1">Customer Reviews</h3>
            <p class="text-muted small mb-0">What clients are saying about your services</p>
        </div>
        <div class="text-end">
            <div class="text-muted small fw-bold">Average Rating</div>
            <div class="h4 fw-bold mb-0 text-primary">
                <i class="bi bi-star-half me-1"></i> Excellent
            </div>
        </div>
    </div>

    <?php if (!$reviews): ?>
        <div class="review-card text-center py-5">
            <i class="bi bi-chat-square-text text-muted mb-3 d-block" style="font-size: 3rem;"></i>
            <h5 class="text-muted">No reviews yet.</h5>
            <p class="small text-muted mb-0">Reviews from your customers will appear here.</p>
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
            <div class="review-card">
                <div class="d-flex gap-3 align-items-start">
                    <div class="avatar-circle">
                        <?= strtoupper(substr($r['customer_name'], 0, 1)) ?>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="customer-name"><?= htmlspecialchars($r['customer_name']) ?></span>
                            <span class="review-date"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                        </div>
                        
                        <div class="mb-2">
                            <span class="service-tag me-2">
                                <i class="bi bi-briefcase me-1"></i>
                                <?= htmlspecialchars($r['service_title']) ?>
                            </span>
                        </div>

                        <div class="mb-3">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="bi bi-star-fill <?= $i <= $r['rating'] ? 'star-fill' : 'star-empty' ?>"></i>
                            <?php endfor; ?>
                        </div>

                        <div class="comment-box">
                            "<?= htmlspecialchars($r['comment']) ?>"
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>