<?php
require "../includes/auth_check.php";
require "../config/db.php";
require "../config/razorpay.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data['order_id'];

$user_id = $_SESSION['user_id'];

/* Verify Order Belongs to Customer */
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE id = ? AND customer_id = ? AND payment_status = 'pending'
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['status'=>'error','message'=>'Invalid order']);
    exit;
}

$amount = $order['total_amount'] * 100;

/* Create Razorpay Order */
$razorpayOrder = $api->order->create([
    'receipt' => 'order_'.$order_id,
    'amount' => $amount,
    'currency' => 'INR'
]);

$razorpayOrderId = $razorpayOrder['id'];

/* Save Razorpay Order ID */
$pdo->prepare("
    UPDATE orders 
    SET transaction_id = ? 
    WHERE id = ?
")->execute([$razorpayOrderId, $order_id]);

echo json_encode([
    'status' => 'success',
    'key' => RAZORPAY_KEY_ID,
    'amount' => $amount,
    'razorpay_order_id' => $razorpayOrderId
]);
