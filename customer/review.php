<?php
require "../includes/auth_check.php";
require "../config/db.php";

if ($_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT o.id, a.service_id, s.title
    FROM orders o
    JOIN appointments a ON o.id = a.order_id
    JOIN services s ON a.service_id = s.id
    WHERE o.id = ? AND o.customer_id = ? AND o.booking_status = 'Completed'
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Invalid or incomplete booking.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);

    $pdo->prepare("
        INSERT INTO reviews (order_id, service_id, customer_id, rating, review)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([
        $order_id,
        $order['service_id'],
        $user_id,
        $rating,
        $review
    ]);

    header("Location: orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Review | ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --star-color: #ffc107;
            --slate-100: #f1f5f9;
            --slate-700: #334155;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .review-container {
            max-width: 550px;
            width: 100%;
            padding: 20px;
        }

        .review-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.05);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: var(--slate-100);
            color: var(--primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 20px;
        }

        /* Star Rating System */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
            margin: 20px 0 30px;
        }

        .star-rating input { display: none; }

        .star-rating label {
            font-size: 2.5rem;
            color: #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: var(--star-color);
            transform: scale(1.1);
        }

        /* Form Elements */
        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--slate-700);
            letter-spacing: 0.5px;
        }

        .form-control {
            border-radius: 16px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            background: #fbfcfd;
            transition: 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
            border-color: var(--primary);
        }

        .btn-submit {
            background: var(--primary);
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: #3651d1;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -5px rgba(67, 97, 238, 0.4);
        }

        .btn-cancel {
            background: transparent;
            color: #94a3b8;
            border: none;
            font-weight: 600;
            margin-top: 15px;
        }

        .btn-cancel:hover { color: #ef4444; }
    </style>
</head>
<body>

<div class="review-container">
    <div class="review-card text-center">
        <div class="service-icon">
            <i class="bi bi-chat-left-heart"></i>
        </div>
        
        <h3 class="fw-800 text-dark mb-1">How was your service?</h3>
        <p class="text-muted small mb-4">Your feedback helps <strong><?= htmlspecialchars($order['merchant_name'] ?? 'the merchant') ?></strong> improve.</p>

        <form method="POST">
            <select name="rating" id="real-rating-select" style="display:none;" required>
                <option value="">Select Rating</option>
                <option value="5">5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
            </select>

            <div class="star-rating">
                <input type="radio" id="star5" name="visual-rate" value="5" onclick="syncRating(5)"><label for="star5" class="bi bi-star-fill"></label>
                <input type="radio" id="star4" name="visual-rate" value="4" onclick="syncRating(4)"><label for="star4" class="bi bi-star-fill"></label>
                <input type="radio" id="star3" name="visual-rate" value="3" onclick="syncRating(3)"><label for="star3" class="bi bi-star-fill"></label>
                <input type="radio" id="star2" name="visual-rate" value="2" onclick="syncRating(2)"><label for="star2" class="bi bi-star-fill"></label>
                <input type="radio" id="star1" name="visual-rate" value="1" onclick="syncRating(1)"><label for="star1" class="bi bi-star-fill"></label>
            </div>

            <div class="text-start mb-4">
                <label class="form-label">Tell us more</label>
                <textarea name="review" class="form-control" rows="4" placeholder="What did you like? Was the provider on time?" required></textarea>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-submit">
                    Post My Review
                </button>
                <a href="orders.php" class="btn btn-cancel">
                    Maybe Later
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // This script syncs the visual stars to your hidden PHP-ready select menu
    function syncRating(val) {
        document.getElementById('real-rating-select').value = val;
    }
</script>

</body>
</html>