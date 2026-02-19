<?php
session_start();
require "../config/db.php";
require "../config/razorpay.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: categories.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid service selected.");
}

$service_id = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT s.*, m.merchant_name, m.id AS merchant_id 
    FROM services s 
    JOIN merchants m ON s.merchant_id = m.id 
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found.");
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout – <?= htmlspecialchars($service['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-700: #334155;
            --slate-900: #0f172a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f7fa;
            color: var(--slate-900);
        }

        /* Hero/Breadcrumb Section */
        .checkout-header {
            background: white;
            padding: 2rem 0;
            border-bottom: 1px solid var(--slate-200);
            margin-bottom: 3rem;
        }

        /* Card Styling */
        .glass-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--slate-200);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .summary-card {
            position: sticky;
            top: 2rem;
            background: var(--slate-900);
            color: white;
            border-radius: 24px;
            padding: 2rem;
        }

        /* Form Styling */
        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--slate-700);
            margin-bottom: 0.75rem;
        }

        .input-group-text {
            background: var(--slate-50);
            border-right: none;
            color: var(--primary);
            border-radius: 12px 0 0 12px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid var(--slate-200);
            font-weight: 500;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .input-group .form-control {
            border-left: none;
        }

        /* Button Styling */
        .btn-pay {
            background: var(--primary);
            border: none;
            padding: 16px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1.5rem;
        }

        .btn-pay:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
        }

        /* Service Thumbnail */
        .service-img-mini {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
        }

        .badge-step {
            width: 28px;
            height: 28px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="checkout-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="categories.php" class="text-decoration-none">Services</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
        <h1 class="fw-800 h3 mb-0">Confirm Your Booking</h1>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <form id="bookingForm" class="glass-card">
                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">

                <div class="mb-4">
                    <h5 class="fw-bold mb-4"><span class="badge-step">1</span> Schedule Appointment</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Preferred Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="service_date" min="<?= date('Y-m-d') ?>" required class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                <select name="service_time" class="form-select" required>
                                    <option value="">Time Slot</option>
                                    <option>09:00 AM</option>
                                    <option>10:00 AM</option>
                                    <option>11:30 AM</option>
                                    <option>01:00 PM</option>
                                    <option>03:00 PM</option>
                                    <option>05:00 PM</option>
                                    <option>07:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
<hr class="my-4 opacity-50">

<div class="mb-4">
    <h5 class="fw-bold mb-4">
        <span class="badge-step">2</span> Contact Information
    </h5>

    <label class="form-label">Contact Number</label>
    <div class="input-group">
        <span class="input-group-text">
            <i class="bi bi-telephone"></i>
        </span>
        <input type="tel" 
               name="contact_no" 
               class="form-control" 
               placeholder="Enter your mobile number"
               pattern="[0-9]{10}"
               maxlength="10"
               required>
    </div>
</div>

                <hr class="my-4 opacity-50">

                <div class="mb-2">
                    <h5 class="fw-bold mb-4"><span class="badge-step">3</span> Service Address</h5>
                    <label class="form-label">Where should the merchant arrive?</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                        <textarea name="address" rows="3" class="form-control" placeholder="House No, Building Name, Area, Pincode" required></textarea>
                    </div>
                    <p class="text-muted small mt-2"><i class="bi bi-info-circle me-1"></i> Ensure someone is available at the location during the selected slot.</p>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="summary-card">
                <h5 class="fw-bold mb-4">Order Summary</h5>
                
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-white p-1 rounded-3">
                        <div class="service-img-mini bg-primary d-flex align-items-center justify-content-center text-white">
                            <i class="bi bi-tools fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($service['title']) ?></h6>
                        <span class="text-white-50 small">By <?= htmlspecialchars($service['merchant_name']) ?></span>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50">Service Price</span>
                    <span>₹<?= number_format($service['price'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-white-50">Taxes & Fees</span>
                    <span>₹0.00</span>
                </div>
                
                <hr class="my-3 border-secondary">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold">Amount Payable</span>
                    <span class="h3 fw-800 mb-0">₹<?= number_format($service['price'], 2) ?></span>
                </div>

                <div class="alert alert-light bg-opacity-10 border-0 text-black small rounded-4">
                    <i class="bi bi-shield-lock-fill me-2 text-info"></i> 
                    Secure Payment powered by Razorpay.
                </div>

                <button type="submit" form="bookingForm" class="btn btn-primary btn-pay text-white">
                    Confirm & Pay Now
                </button>
                <button type="button" id="payLaterBtn" class="btn btn-outline-light w-100 mt-2">
    Pay Later
</button>


                <div class="text-center mt-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" height="20" style="filter: brightness(0) invert(1);" alt="Razorpay">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('bookingForm').addEventListener('submit', function(e){
    e.preventDefault(); // stop normal form submit

    const form = document.getElementById('bookingForm');
    const formData = new FormData(form);

    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.status === 'success'){

            const options = {
                key: data.key,
                amount: data.amount,
                currency: "INR",
                name: "ServiceHub",
                description: "Service Booking Payment",
                order_id: data.order_id,
                handler: function (response){
                    // redirect to verify payment
                    window.location.href = 
                        "verify_payment.php?payment_id=" 
                        + response.razorpay_payment_id 
                        + "&order_id=" + data.db_order_id;
                },
                theme: { color: "#6366f1" }
            };

            const rzp = new Razorpay(options);
            rzp.open();

        } else {
            alert(data.message);
        }

    })
    .catch(err => alert("Error: " + err));
});

// Pay Later button
document.getElementById('payLaterBtn').addEventListener('click', function(){
    const form = document.getElementById('bookingForm');
    const formData = new FormData(form);

    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            // Skip payment, go to orders page
            window.location.href = "../customer/orders.php";
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert("Error: " + err));
});
</script>

</body>
</html>  