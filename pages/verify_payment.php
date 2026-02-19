<?php
session_start();
require '../config/db.php';

// Validate login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit;
}

// Validate parameters
if (!isset($_GET['payment_id']) || !isset($_GET['order_id'])) {
    die("Invalid payment response.");
}

$payment_id = $_GET['payment_id'];
$order_id   = intval($_GET['order_id']);
$customer_id = $_SESSION['user_id'];

// Check if order exists and belongs to customer
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$order_id, $customer_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Booking not found.");
}

// Update payment if not already paid
if ($order['payment_status'] !== 'Paid') {

    $update = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'Paid',
            booking_status = 'Completed',
            transaction_id = ?
        WHERE id = ?
    ");

    $update->execute([$payment_id, $order_id]);
}

// Redirect to booking details page
header("Location: my_bookings.php?order_id=" . $order_id);
exit;
