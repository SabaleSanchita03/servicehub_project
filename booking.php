<?php
require "config/db.php";
require "includes/auth_check.php"; // customer must be logged in

$service_id = $_GET['service_id'] ?? null;
if (!$service_id) {
    die("Invalid service");
}

/* Fetch service info */
$stmt = $pdo->prepare("
    SELECT s.*, m.business_name
    FROM services s
    JOIN merchants m ON s.merchant_id = m.id
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die("Service not found");
}

/* Handle booking */
if (isset($_POST['book'])) {

    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, service_id, booking_date, booking_time, address)
        VALUES (?,?,?,?,?)
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $service_id,
        $_POST['date'],
        $_POST['time'],
        $_POST['address']
    ]);

    header("Location: booking_success.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Booking – ServiceHub</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6366f1;
            --bg-body: #f8fafc;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-body);
            color: #1e293b;
        }

        .booking-container {
            max-width: 550px;
            margin: 3rem auto;
        }

        .card-booking {
            background: #ffffff;
            border-radius: 28px;
            border: none;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            padding: 2.5rem;
        }

        .service-summary {
            background-color: #f1f5f9;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-confirm {
            background-color: var(--primary-color);
            border: none;
            border-radius: 14px;
            padding: 1rem;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-confirm:hover {
            background-color: #4f46e5;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
        }

        .step-active { color: var(--primary-color); }
    </style>
</head>

<body>
<div class="container">
    <div class="booking-container">
        
        <a href="service_details.php?id=<?= $service_id ?>" class="text-decoration-none text-muted small mb-4 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Back to Service
        </a>

        <div class="card-booking">
            <div class="step-indicator">
                <span>Select Service</span>
                <i class="bi bi-chevron-right"></i>
                <span class="step-active">Booking Details</span>
                <i class="bi bi-chevron-right"></i>
                <span>Payment</span>
            </div>

            <h3 class="fw-bold text-center mb-4">Complete Your Booking</h3>

            <div class="service-summary d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold mb-0 text-truncate" style="max-width: 200px;"><?= htmlspecialchars($service['title']) ?></h6>
                    <p class="text-muted small mb-0">by <?= htmlspecialchars($service['business_name']) ?></p>
                </div>
                <div class="text-end">
                    <span class="fw-bold text-primary fs-5">₹<?= number_format($service['price'], 2) ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3"></i></span>
                            <input type="date" name="date" class="form-control border-start-0" required
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Time</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-clock"></i></span>
                            <input type="time" name="time" class="form-control border-start-0" required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Service Address</label>
                    <textarea name="address" class="form-control" rows="3"
                              placeholder="Where should the service be provided?" required></textarea>
                    <div class="form-text mt-2 small">
                        <i class="bi bi-shield-lock-fill me-1"></i> Your address is shared only with the merchant.
                    </div>
                </div>

                <button name="book" class="btn btn-primary btn-confirm w-100 mb-3">
                    Confirm & Proceed to Pay
                </button>

                <p class="text-center text-muted small mb-0">
                    By clicking "Confirm", you agree to our 
                    <a href="#" class="text-decoration-none">Terms of Service</a>
                </p>
            </form>
        </div>

        <div class="text-center mt-4">
            <img src="https://cdn-icons-png.flaticon.com/512/1004/1004617.png" width="20" class="opacity-50 me-2">
            <span class="small text-muted">Secure 256-bit SSL Encrypted Checkout</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>