<?php
require "../includes/auth_check.php";
require "../config/db.php";

/* Allow only customers */
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in customer info
$stmt_user = $pdo->prepare("SELECT name FROM users WHERE id=?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Success message flag
$success_msg = false;
$error_msg = "";

// Handle new review submission
if(isset($_POST['submit_review'])) {

    // Ensure service_id is set and in expected format
    if(!empty($_POST['service_id']) && strpos($_POST['service_id'], ':') !== false) {
        list($booking_id, $service_id) = explode(':', $_POST['service_id']);

        // Validate IDs are numeric
        if(is_numeric($booking_id) && is_numeric($service_id)) {
            $rating = intval($_POST['rating']);
            $comment = trim($_POST['comment']);

            // Insert review safely
            $stmt_insert = $pdo->prepare("
                INSERT INTO reviews (order_id, customer_id, service_id, rating, review, created_at)
VALUES (?, ?, ?, ?, ?, NOW())

            ");
            $stmt_insert->execute([$booking_id, $user_id, $service_id, $rating, $comment]);
            $success_msg = true;
        } else {
            $error_msg = "Invalid service selection.";
        }
    } else {
        $error_msg = "Please select a valid service.";
    }
}

// Fetch reviews written by this customer
$stmt_reviews = $pdo->prepare("
    SELECT r.*, s.title AS service_title, m.merchant_name AS business_name
    FROM reviews r
    JOIN services s ON r.service_id = s.id
    JOIN merchants m ON s.merchant_id = m.id
    WHERE r.customer_id = ?

    ORDER BY r.created_at DESC
");
$stmt_reviews->execute([$user_id]);
$reviews = $stmt_reviews->fetchAll();

// Fetch completed bookings without reviews
$stmt_completed = $pdo->prepare("
    SELECT b.id AS booking_id, s.id AS service_id, 
           s.title AS service_title, m.merchant_name AS business_name
    FROM orders b
    JOIN appointments a ON b.id = a.order_id
    JOIN services s ON a.service_id = s.id
    JOIN merchants m ON s.merchant_id = m.id
    LEFT JOIN reviews r ON r.order_id = b.id
    WHERE b.customer_id = ? 
    AND b.booking_status='Completed' 
    AND r.order_id IS NULL
");

$stmt_completed->execute([$user_id]);
$completed_services = $stmt_completed->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reviews – ServiceHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg-body: #f8fafc;
            --star-gold: #f59e0b;
            --slate-300: #cbd5e1;
            --slate-600: #475569;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg-body); 
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        .page-header {
            background: white;
            padding: 2.5rem 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 3rem;
        }

        /* Review Form Styling */
        .card-review-form {
            background: #ffffff;
            border-radius: 28px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .form-side-accent {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            padding: 2rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Interactive Star Rating CSS */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 8px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 2rem;
            color: var(--slate-300);
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: var(--star-gold);
            transform: scale(1.1);
        }

        /* Review History Cards */
        .history-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
        }
        .history-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            border-color: var(--primary);
        }

        .avatar-circle {
            width: 48px;
            height: 48px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .quote-bubble {
            background: #f1f5f9;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            position: relative;
            font-style: italic;
            color: var(--slate-600);
        }

        .btn-modern {
            border-radius: 14px;
            padding: 12px 24px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .form-label { font-weight: 700; color: #475569; font-size: 0.9rem; }
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px;
        }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); border-color: var(--primary); }
    </style>
</head>
<body>

<header class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle"><i class="bi bi-star-half"></i></div>
            <div>
                <h1 class="h3 fw-800 mb-0">Review Center</h1>
                <p class="text-muted small mb-0">Share your experience with the community</p>
            </div>
        </div>
    </div>
</header>

<div class="container pb-5">

    <?php if($success_msg): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div><strong>Great!</strong> Your review has been published.</div>
        </div>
    <?php endif; ?>

    <?php if($completed_services): ?>
        <div class="card-review-form mb-5">
            <div class="row g-0">
                <div class="col-lg-4 form-side-accent">
                    <i class="bi bi-chat-quote-fill fs-1 mb-3 opacity-50"></i>
                    <h4 class="fw-800">Your Feedback Matters</h4>
                    <p class="mb-0 opacity-75">Tell us about your recent service to help providers improve and other customers choose better.</p>
                </div>
                <div class="col-lg-8 p-4 p-md-5">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Which service are you reviewing?</label>
                                <select name="service_id" class="form-select" required>
                                    <option value="">Select a recent booking...</option>
                                    <?php foreach($completed_services as $cs): ?>
                                        <option value="<?= $cs['booking_id'] ?>:<?= $cs['service_id'] ?>">
                                            <?= htmlspecialchars($cs['service_title']) ?> — <?= htmlspecialchars($cs['business_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-12 my-3">
                                <label class="form-label d-block">Rating</label>
                                <div class="star-rating">
                                    <input type="radio" name="rating" id="star5" value="5" required /><label for="star5" class="bi bi-star-fill"></label>
                                    <input type="radio" name="rating" id="star4" value="4" /><label for="star4" class="bi bi-star-fill"></label>
                                    <input type="radio" name="rating" id="star3" value="3" /><label for="star3" class="bi bi-star-fill"></label>
                                    <input type="radio" name="rating" id="star2" value="2" /><label for="star2" class="bi bi-star-fill"></label>
                                    <input type="radio" name="rating" id="star1" value="1" /><label for="star1" class="bi bi-star-fill"></label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Review Details</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="Describe your experience..." required></textarea>
                            </div>

                            <div class="col-md-12 pt-2">
                                <button type="submit" name="submit_review" class="btn btn-primary btn-modern w-100">
                                    Submit Public Review
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="fw-800 mb-0">Your Review History</h5>
        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill"><?= count($reviews) ?> Reviews</span>
    </div>

    <?php if(!$reviews): ?>
        <div class="text-center py-5 bg-white rounded-4 border border-dashed">
            <i class="bi bi-journal-x fs-1 text-muted opacity-25"></i>
            <p class="mt-3 text-muted">You haven't written any reviews yet.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach($reviews as $r): ?>
                <div class="col-md-6">
                    <div class="history-card p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle"><i class="bi bi-briefcase"></i></div>
                                <div>
                                    <h6 class="fw-800 mb-0 text-dark"><?= htmlspecialchars($r['service_title']) ?></h6>
                                    <span class="text-primary small fw-600">@<?= htmlspecialchars($r['business_name']) ?></span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-1">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                        <i class="bi bi-star-fill <?= $i <= $r['rating'] ? 'text-warning' : 'text-light' ?>" style="font-size: 0.8rem;"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-muted smaller fw-bold" style="font-size: 0.7rem;"><?= date('M d, Y', strtotime($r['created_at'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="quote-bubble">
                            <i class="bi bi-quote fs-4 opacity-25 position-absolute top-0 start-0 m-1"></i>
                            <p class="mb-0 small pe-3">"<?= htmlspecialchars($r['review']) ?>"</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>