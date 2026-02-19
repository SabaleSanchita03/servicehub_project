<?php
session_start();

// If the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Capture the current page and any GET parameters
    $currentUrl = $_SERVER['REQUEST_URI']; 
    // Encode the URL so it can be passed as a redirect parameter
    $redirect = urlencode($currentUrl);
    header("Location: ../auth/login.php?redirect=$redirect");
    exit;
}

// Role-based access control
$allowed_roles = ['customer', 'merchant', 'admin']; // default allowed roles per page

if (isset($page_role) && !in_array($_SESSION['role'], $page_role)) {
    // redirect based on actual user role
    switch($_SESSION['role']) {
        case 'customer':
            header("Location: ../customer/dashboard.php"); 
            break;
        case 'merchant':
            header("Location: ../merchant/dashboard.php"); 
            break;
        case 'admin':
            header("Location: ../admin/dashboard.php"); 
            break;
        default:
            header("Location: ../auth/login.php");
    }
    exit;
}
?>
