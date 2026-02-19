<?php
require 'includes/auth.php';
require '../config/db.php';

$page_title = "Manage Services";

/* ======================
   FETCH SERVICES
====================== */
$services = $pdo->query("
    SELECT 
        services.*, 
        merchants.merchant_name,
        categories.name AS category_name
    FROM services
    JOIN merchants ON services.merchant_id = merchants.id
    JOIN categories ON services.category_id = categories.id
    ORDER BY services.id DESC
")->fetchAll();

require 'includes/header.php';
?>

<style>
    /* Message Alerts */
    .alert {
        padding: 14px 20px;
        border-radius: 12px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        animation: slideIn 0.3s ease-out;
    }
    .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .alert-error { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Table Enhancements */
    .table-container {
        background: white;
        margin-top: 25px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
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
        vertical-align: middle;
        color: #1e293b;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background-color: #fbfcfe; }

    /* Service Visuals */
    .service-img {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        object-fit: cover;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    .img-placeholder {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 1.2rem;
    }

    /* Status & Category Badges */
    .badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        background: #eef2ff;
        color: #6366f1;
        display: inline-block;
    }

    .merchant-link {
        color: #1e293b;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .merchant-link:hover { color: #6366f1; }

    .price-tag {
        font-weight: 800;
        color: #0f172a;
        font-size: 1rem;
    }

    .duration-text {
        color: #64748b;
        font-weight: 500;
        font-size: 0.85rem;
    }
</style>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
    <div>
        <h2 style="font-weight:800; letter-spacing: -1px; margin:0;">Manage Services</h2>
        <p style="color:#64748b; margin:5px 0 0;">Review all professional offerings across categories.</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="bi bi-exclamation-octagon-fill"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="table-container">
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Service Details</th>
    <th>Merchant</th>
    <th>Category</th>
    <th>Pricing</th>
    <th>Duration</th>
    <th>Posted Date</th>
</tr>
</thead>

<tbody>
<?php if (count($services) > 0): ?>
    <?php foreach ($services as $s): ?>
    <tr>
        <td style="font-family: monospace; color: #94a3b8; font-weight: 700;">#<?= $s['id'] ?></td>

        <td>
            <div style="display:flex; align-items:center; gap:14px;">
<?php if (!empty($s['image']) && file_exists("../assets/services/" . $s['image'])): ?>
    <img src="../assets/services/<?= htmlspecialchars($s['image']) ?>" class="service-img">
<?php else: ?>
    <div class="img-placeholder"><i class="bi bi-image"></i></div>
<?php endif; ?>

                <div>
                    <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($s['title']) ?></div>
                    <div style="color:#64748b; font-size:0.8rem; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($s['location']) ?>
                    </div>
                </div>
            </div>
        </td>

        <td>
            <a href="#" class="merchant-link">
                <i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($s['merchant_name']) ?>
            </a>
        </td>

        <td><span class="badge"><?= htmlspecialchars($s['category_name']) ?></span></td>

        <td><span class="price-tag">₹<?= number_format($s['price'], 0) ?></span></td>

        <td><span class="duration-text"><i class="bi bi-clock me-1"></i> <?= htmlspecialchars($s['duration']) ?></span></td>

        <td style="color:#94a3b8; font-size:0.85rem; font-weight:500;">
            <?= date('M d, Y', strtotime($s['created_at'])) ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" style="text-align:center; padding:60px;">
            <div style="color:#94a3b8; font-size:1.1rem; font-weight:600;">No services currently listed.</div>
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>
</div>

<?php require 'includes/footer.php'; ?>