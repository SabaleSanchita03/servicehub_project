<?php
session_start();
require "../config/db.php";

header('Content-Type: application/json');

// Ensure merchant is logged in
if(!isset($_SESSION['merchant_id'])){
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Validate input
$order_id = $_POST['order_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;

$allowed_status = ['Pending','Completed','Cancelled','Rejected'];

if(!$order_id || !$new_status || !in_array($new_status, $allowed_status)){
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Update order if it belongs to this merchant
$stmt = $pdo->prepare("UPDATE orders SET booking_status=? WHERE id=? AND merchant_id=?");
$updated = $stmt->execute([$new_status, $order_id, $_SESSION['merchant_id']]);

if($updated){
    echo json_encode(['success' => true, 'new_status' => $new_status]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}
