<?php
require 'includes/auth.php';
require '../config/db.php';

$page_title = "Manage Merchants";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';

/* ======================
   TOGGLE VERIFICATION
====================== */
if (isset($_GET['verify'])) {
    $id = (int) $_GET['verify'];

    $stmt = $pdo->prepare("UPDATE merchants 
                           SET is_verified = IF(is_verified = 1, 0, 1) 
                           WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: merchants.php");
    exit();
}

/* ======================
   DELETE MERCHANT
====================== */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    try {
        $stmt = $pdo->prepare("DELETE FROM merchants WHERE id = ?");
        $stmt->execute([$id]);

        $success = "Merchant deleted successfully.";

    } catch (PDOException $e) {
        $error = "Cannot delete merchant because orders exist for this merchant.";
    }
}

/* ======================
   FETCH MERCHANTS
====================== */
$merchants = $pdo->query("
    SELECT merchants.*, users.email 
    FROM merchants
    JOIN users ON merchants.user_id = users.id
    ORDER BY merchants.id DESC
")->fetchAll();

require 'includes/header.php';
?>


<style>
    /* Message Alerts Styling */
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

    .alert-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .alert-error {
        background: #fff1f2;
        color: #9f1239;
        border: 1px solid #fecdd3;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Table Container Refinement */
    .table-container {
        background: white;
        margin-top: 5px; /* Adjusted since alert adds margin */
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
        font-size: 0.95rem;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover td { background-color: #fbfcfe; }

    /* Merchant Identity Column */
    .merchant-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-sm {
        width: 36px;
        height: 36px;
        background: #eef2ff;
        color: #6366f1;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .pill-approved { background: #ecfdf5; color: #10b981; }
    .pill-pending { background: #fffbeb; color: #f59e0b; }
    
    .dot { width: 6px; height: 6px; border-radius: 50%; }
    .dot-approved { background: #10b981; }
    .dot-pending { background: #f59e0b; }

    /* Action Buttons */
    .action-group {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .btn-action {
        padding: 8px 14px;
        border-radius: 10px;
        border: 1px solid transparent;
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-verify {
        background: #f0fdf4;
        color: #16a34a;
        border-color: #dcfce7;
    }
    
    .btn-verify:hover { background: #16a34a; color: white; }

    .btn-delete {
        background: #fff1f2;
        color: #ef4444;
        border-color: #fee2e2;
    }

    .btn-delete:hover { background: #ef4444; color: white; }

    .merchant-email {
        color: #64748b;
        font-size: 0.85rem;
    }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
    <div>
        <h2 style="font-weight:800; letter-spacing: -1px; margin:0;">Manage Merchants</h2>
        <p style="color: #64748b; margin: 5px 0 0;">Review and verify business partners on the platform.</p>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
<?php endif; ?>

<div class="table-container">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Merchant Details</th>
            <th>Email Address</th>
            <th>Verification</th>
            <th style="text-align:right;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($merchants as $m): ?>
        <tr>
            <td style="font-family: monospace; color: #94a3b8; font-weight: 700;">#<?= $m['id'] ?></td>
            
            <td>
                <div class="merchant-profile">
                    <div class="avatar-sm">
                        <?= substr(htmlspecialchars($m['merchant_name']), 0, 2) ?>
                    </div>
                    <span style="font-weight:700;"><?= htmlspecialchars($m['merchant_name']) ?></span>
                </div>
            </td>

            <td><span class="merchant-email"><?= htmlspecialchars($m['email']) ?></span></td>

            <td>
                <?php if ($m['is_verified'] == 1): ?>
                    <div class="status-pill pill-approved">
                        <span class="dot dot-approved"></span> Approved
                    </div>
                <?php else: ?>
                    <div class="status-pill pill-pending">
                        <span class="dot dot-pending"></span> Pending
                    </div>
                <?php endif; ?>
            </td>

            <td>
                <div class="action-group">
                    <a href="?verify=<?= $m['id'] ?>" class="btn-action btn-verify">
                        <i class="bi bi-shield-check"></i> Toggle
                    </a>

                    <a href="?delete=<?= $m['id'] ?>" 
                       onclick="return confirm('Delete this merchant?')" 
                       class="btn-action btn-delete">
                        <i class="bi bi-trash3"></i> Delete
                    </a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require 'includes/footer.php'; ?>