<?php
require 'includes/auth.php';
require '../config/db.php';

$page_title = "Manage Customers";

$error = '';
$success = '';

/* ======================
   DELETE CUSTOMER
====================== */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
        $stmt->execute([$id]);

        $success = "Customer deleted successfully.";
    } catch (PDOException $e) {
        $error = "Cannot delete customer.";
    }
}

/* ======================
   FETCH CUSTOMERS
====================== */
$customers = $pdo->query("
    SELECT * FROM users 
    WHERE role = 'customer'
    ORDER BY id DESC
")->fetchAll();

require 'includes/header.php';
?>


<?php if (!empty($success)): ?>
<div style="background:#ecfdf5;color:#16a34a;padding:12px 18px;border-radius:10px;margin:15px 0;">
    <?= $success ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div style="background:#fff1f2;color:#ef4444;padding:12px 18px;border-radius:10px;margin:15px 0;">
    <?= $error ?>
</div>
<?php endif; ?>



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

    /* Profile UI */
    .customer-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-sm {
        width: 38px;
        height: 38px;
        background: #f1f5f9;
        color: #6366f1;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Button Styling */
    .btn-delete {
        padding: 8px 16px;
        border-radius: 10px;
        background: #fff1f2;
        color: #ef4444;
        border: 1px solid #fee2e2;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-delete:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    .id-text {
        font-family: monospace;
        color: #94a3b8;
        font-weight: 700;
    }
</style>

<div style="margin-bottom: 5px;">
    <h2 style="font-weight:800; letter-spacing: -1px; margin:0;">Manage Customers</h2>
    <p style="color: #64748b; margin: 5px 0 0;">View and manage registered users on the ServiceHub platform.</p>
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
            <th>Customer Name</th>
            <th>Email Address</th>
            <th style="text-align:right;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $c): ?>
        <tr>
            <td><span class="id-text">#<?= $c['id'] ?></span></td>

            <td>
                <div class="customer-info">
                    <div class="avatar-sm">
                        <?= strtoupper(substr(htmlspecialchars($c['name']), 0, 2)) ?>
                    </div>
                    <span style="font-weight:700;"><?= htmlspecialchars($c['name']) ?></span>
                </div>
            </td>

            <td style="color:#64748b; font-size: 0.9rem;">
                <?= htmlspecialchars($c['email']) ?>
            </td>

            <td style="text-align:right;">
                <a href="?delete=<?= $c['id'] ?>" 
                   onclick="return confirm('Delete this customer? This action cannot be undone.')" 
                   class="btn-delete">
                   <i class="bi bi-trash3"></i> Delete
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require 'includes/footer.php'; ?>