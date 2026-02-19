<?php
require 'includes/auth.php';
require '../config/db.php';

$page_title = "Manage Orders";

/* ======================
   FETCH ORDERS
====================== */
$orders = $pdo->query("
    SELECT 
        orders.*, 
        users.name AS customer_name,
        merchants.merchant_name
    FROM orders
    JOIN users ON orders.customer_id = users.id
    JOIN merchants ON orders.merchant_id = merchants.id
    ORDER BY orders.id DESC
")->fetchAll();

require 'includes/header.php';
?>

<style>
.table-container {
    background: white;
    margin-top: 25px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
}

table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

th {
    background: #f8fafc;
    padding: 18px 25px;
    text-align: left;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #64748b;
    font-weight: 800;
    border-bottom: 1px solid #e2e8f0;
}

td {
    padding: 16px 25px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.95rem;
    color: #1e293b;
}

tr:last-child td { border-bottom: none; }
tr:hover td { background: #fbfcfe; }

.status-pill {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-block;
}

/* Payment Status */
.pending { background:#fffbeb; color:#f59e0b; }
.paid { background:#ecfdf5; color:#16a34a; }
.failed { background:#fff1f2; color:#ef4444; }

/* Booking Status */
.completed { background:#ecfdf5; color:#16a34a; }
.cancelled { background:#fff1f2; color:#ef4444; }
.rejected { background:#f3f4f6; color:#6b7280; }

.amount {
    font-weight: 700;
}
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
    <div>
        <h2 style="font-weight:800;letter-spacing:-1px;margin:0;">
            Manage Orders
        </h2>
        <p style="color:#64748b;margin:5px 0 0;">
            View and monitor all platform bookings.
        </p>
    </div>
</div>

<div class="table-container">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Merchant</th>
            <th>Total Amount</th>
            <th>Payment</th>
            <th>Booking</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td style="font-family:monospace;color:#94a3b8;font-weight:700;">
                    #<?= $o['id'] ?>
                </td>

                <td><?= htmlspecialchars($o['customer_name']) ?></td>

                <td><?= htmlspecialchars($o['merchant_name']) ?></td>

                <td class="amount">
                    ₹<?= number_format($o['total_amount'], 2) ?>
                </td>

                <td>
                    <span class="status-pill <?= strtolower($o['payment_status']) ?>">
                        <?= ucfirst($o['payment_status']) ?>
                    </span>
                </td>

                <td>
                    <span class="status-pill <?= strtolower($o['booking_status']) ?>">
                        <?= $o['booking_status'] ?>
                    </span>
                </td>

                <td>
                    <?= date('M d, Y', strtotime($o['created_at'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
                    No orders found.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?php require 'includes/footer.php'; ?>
