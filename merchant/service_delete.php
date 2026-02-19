<?php
require "../includes/auth_check.php";
require "../config/db.php";

$user_id = $_SESSION['user_id'];
$service_id = $_GET['id'];

$stmt = $pdo->prepare("
    DELETE s FROM services s
    JOIN merchants m ON s.merchant_id = m.id
    WHERE s.id = ? AND m.user_id = ?
");
$stmt->execute([$service_id, $user_id]);

header("Location: services.php");
