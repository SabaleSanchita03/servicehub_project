<?php
require "../includes/auth_check.php";
require "../config/db.php";
require "../config/razorpay.php";

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'customer') {
    echo json_encode(['status'=>'error','message'=>'Login required']);
    exit;
}

if (!isset($_POST['service_id'], $_POST['service_date'], $_POST['service_time'], $_POST['address'], $_POST['contact_no'])) {

    echo json_encode(['status'=>'error','message'=>'All fields are required']);
    exit;
}

$service_id   = $_POST['service_id'];
$service_date = $_POST['service_date'];
$service_time = date("H:i", strtotime($_POST['service_time']));
$contact_no = $_POST['contact_no'];
$address      = $_POST['address'];
$customer_id  = $_SESSION['user_id'];
$pay_later    = isset($_POST['pay_later']); // 👈 IMPORTANT

// Fetch service
$stmt = $pdo->prepare("
    SELECT s.*, m.id AS merchant_id 
    FROM services s 
    JOIN merchants m ON s.merchant_id = m.id 
    WHERE s.id=?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    echo json_encode(['status'=>'error','message'=>'Service not found']);
    exit;
}

$price = $service['price'];
$merchant_id = $service['merchant_id'];

try {
    $pdo->beginTransaction();

    // Create Order (default payment pending)
    $stmt_order = $pdo->prepare("
        INSERT INTO orders 
        (customer_id, merchant_id, total_amount, payment_status, booking_status)
        VALUES (?, ?, ?, 'pending', 'Pending')
    ");
    $stmt_order->execute([$customer_id, $merchant_id, $price]);
    $order_id = $pdo->lastInsertId();

    // Create Appointment
    $stmt_appointment = $pdo->prepare("
        INSERT INTO appointments 
        (order_id, service_id, service_date, service_time, address) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt_appointment->execute([$order_id, $service_id, $service_date, $service_time, $address]);

    // 🔹 IF PAY LATER → STOP HERE
    if ($pay_later) {
        $pdo->commit();

        echo json_encode([
            'status'=>'success',
            'pay_later'=>true
        ]);
        exit;
    }

    // 🔹 OTHERWISE → CREATE RAZORPAY ORDER
    $razorpayOrder = $api->order->create([
        'receipt'  => 'order_'.$order_id,
        'amount'   => $price * 100,
        'currency' => 'INR'
    ]);

    $razorpayOrderId = $razorpayOrder['id'];

    // Save Razorpay order ID
    $stmt_update = $pdo->prepare("
        UPDATE orders 
        SET transaction_id=? 
        WHERE id=?
    ");
    $stmt_update->execute([$razorpayOrderId, $order_id]);

    $pdo->commit();

echo json_encode([
    'status'=>'success',
    'amount'=>$price*100,
    'order_id'=>$razorpayOrderId,
    'db_order_id'=>$order_id,
    'key'=> RAZORPAY_KEY_ID
]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'status'=>'error',
        'message'=>'Booking failed: '.$e->getMessage()
    ]);
}
