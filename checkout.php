<?php
session_start();
require "config/db.php";
require "config/razorpay.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

if (!isset($_GET['service_id'])) {
    header("Location: categories.php");
    exit;
}

$service_id = $_GET['service_id'];

$stmt = $pdo->prepare("SELECT s.*, m.merchant_name, m.id AS merchant_id 
                       FROM services s 
                       JOIN merchants m ON s.merchant_id = m.id 
                       WHERE s.id=?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found");
}

$razorpayKeyId = RAZORPAY_KEY_ID;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
        }

        /* Page Layout */
        .checkout-wrapper {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .main-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        /* Form Styling */
        .form-section { padding: 40px; }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }
        
        .section-header i {
            width: 40px;
            height: 40px;
            background: #eef2ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.2rem;
        }

        .input-box { margin-bottom: 20px; }
        .input-box label {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            font-weight: 500;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        /* Sidebar Summary */
        .summary-sidebar {
            background: #0f172a;
            color: #fff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .summary-title {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .service-details h4 { font-weight: 800; margin-bottom: 5px; }
        .merchant-name { color: #94a3b8; font-size: 0.9rem; }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #1e293b;
        }

        .total-price { font-size: 1.75rem; font-weight: 800; }

        .btn-confirm {
            background: var(--primary);
            color: #fff;
            border: none;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 30px;
            transition: 0.3s;
        }

        .btn-confirm:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="checkout-wrapper">
    <div class="main-card">
        <div class="row g-0">
            <div class="col-lg-7">
                <div class="form-section">
                    <div class="section-header">
                        <i class="bi bi-calendar-check"></i>
                        <h3 class="mb-0 fw-bold">Booking Details</h3>
                    </div>

                    <form id="paymentForm">
                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-box">
                                    <label>Appointment Date</label>
                                    <input type="date" name="service_date" min="<?= date('Y-m-d') ?>" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-box">
                                    <label>Preferred Time</label>
                                    <input type="time" name="service_time" required class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="input-box">
                            <label>Service Address</label>
                            <textarea name="address" rows="4" required class="form-control" placeholder="House no, Building, Street, Landmark..."></textarea>
                        </div>

                        <div class="alert alert-info border-0 rounded-4 p-3 small">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Your address helps the merchant reach you on time.
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="summary-sidebar h-100">
                    <div>
                        <div class="summary-title">Order Summary</div>
                        <div class="service-details">
                            <h4><?= htmlspecialchars($service['title']) ?></h4>
                            <div class="merchant-name">
                                <i class="bi bi-shop me-1"></i> 
                                <?= htmlspecialchars($service['merchant_name']) ?>
                            </div>
                        </div>
                        
                        <div class="price-row">
                            <span>Amount Payable</span>
                            <span class="total-price">₹<?= number_format($service['price'], 2) ?></span>
                        </div>
                    </div>

                    <div>
                        <button type="submit" form="paymentForm" class="btn-confirm">
                            Confirm & Pay Now
                        </button>
                        <div class="secure-badge">
                            <i class="bi bi-shield-lock-fill"></i>
                            Secure Payment via Razorpay
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4">
        <a href="javascript:history.back()" class="text-muted text-decoration-none small fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Cancel and go back
        </a>
    </div>
</div>



<script>
document.getElementById('paymentForm').addEventListener('submit', function(e){
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    let formData = new FormData(this);

    fetch('create_order.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            var options = {
                key: "<?= $razorpayKeyId ?>",
                amount: data.amount,
                currency: "INR",
                name: "ServiceHub",
                description: "Booking for <?= htmlspecialchars($service['title']) ?>",
                order_id: data.razorpay_order_id,
                prefill: {
                    name: "<?= $_SESSION['user_name'] ?? '' ?>",
                    email: "<?= $_SESSION['user_email'] ?? '' ?>"
                },
                handler: function (response) {
                    window.location.href = "verify_payment.php?payment_id=" + response.razorpay_payment_id + "&order_id=" + data.db_order_id;
                },
                theme: { color: "#6366f1" },
                modal: { ondismiss: function() { submitBtn.disabled = false; submitBtn.innerText = 'Confirm & Pay Now'; } }
            };

            var rzp = new Razorpay(options);
            rzp.open();
        } else {
            alert(data.message);
            submitBtn.disabled = false;
            submitBtn.innerText = 'Confirm & Pay Now';
        }
    })
    .catch(err => {
        alert("Error: " + err);
        submitBtn.disabled = false;
        submitBtn.innerText = 'Confirm & Pay Now';
    });
});
</script>

</body>
</html>