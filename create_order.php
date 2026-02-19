<?php
session_start();
require "config/db.php";
require "config/razorpay.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Login required']);
    exit;
}

if(!isset($_POST['service_id'], $_POST['service_date'], $_POST['service_time'], $_POST['address'])){
    echo json_encode(['status'=>'error','message'=>'All fields required']);
    exit;
}

$service_id = $_POST['service_id'];
$service_date = $_POST['service_date'];
$service_time = $_POST['service_time'];
$address = $_POST['address'];
$customer_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT s.*, m.id AS merchant_id 
                       FROM services s 
                       JOIN merchants m ON s.merchant_id = m.id 
                       WHERE s.id=?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if(!$service){
    echo json_encode(['status'=>'error','message'=>'Service not found']);
    exit;
}

$price = $service['price'];
$merchant_id = $service['merchant_id'];

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders 
        (customer_id, merchant_id, total_amount, payment_status) 
        VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$customer_id, $merchant_id, $price]);

    $order_id = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare("INSERT INTO appointments 
        (order_id, service_id, service_date, service_time, address) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt2->execute([$order_id, $service_id, $service_date, $service_time, $address]);

    $razorpayOrder = $api->order->create([
        'receipt' => 'order_'.$order_id,
        'amount' => $price * 100,
        'currency' => 'INR'
    ]);

    $stmt3 = $pdo->prepare("UPDATE orders SET transaction_id=? WHERE id=?");
    $stmt3->execute([$razorpayOrder['id'], $order_id]);

    $pdo->commit();

    echo json_encode([
        'status'=>'success',
        'amount'=>$price * 100,
        'razorpay_order_id'=>$razorpayOrder['id'],
        'db_order_id'=>$order_id
    ]);

} catch(Exception $e){
    $pdo->rollBack();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
