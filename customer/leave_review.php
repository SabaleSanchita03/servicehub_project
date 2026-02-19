<?php
require "../includes/auth_check.php";
require "../config/db.php";

/* Only customers allowed */
if ($_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get booking ID
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Check if booking exists and is completed
$stmt = $pdo->prepare("
    SELECT b.booking_id, b.booking_status, s.title AS service_title, m.merchant_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN merchants m ON s.merchant_id = m.id
    WHERE b.booking_id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found or access denied.");
}

if ($booking['booking_status'] !== 'completed') {
    die("You can only leave a review for completed bookings.");
}

// Check if review already exists
$stmt_check = $pdo->prepare("SELECT * FROM reviews WHERE booking_id = ?");
$stmt_check->execute([$booking_id]);
$existing_review = $stmt_check->fetch();

if ($existing_review) {
    die("You have already submitted a review for this booking.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $error = "Rating must be between 1 and 5.";
    } elseif (empty($comment)) {
        $error = "Comment cannot be empty.";
    } else {
        $stmt_insert = $pdo->prepare("
            INSERT INTO reviews (booking_id, user_id, merchant_id, rating, comment, created_at)
            VALUES (?, ?, (SELECT s.merchant_id FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.booking_id = ?), ?, ?, NOW())
        ");
        $stmt_insert->execute([$booking_id, $user_id, $booking_id, $rating, $comment]);

        header("Location: orders.php?review_submitted=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Review – ServiceHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">Leave a Review for <?= htmlspecialchars($booking['service_title']) ?></h2>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Rating</label>
            <select name="rating" class="form-select" required>
                <option value="">Select rating</option>
                <?php for($i=5; $i>=1; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?> Star<?= $i>1?'s':'' ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Comment</label>
            <textarea name="comment" class="form-control" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Review</button>
        <a href="orders.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
</body>
</html>
