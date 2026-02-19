<?php
require 'includes/auth.php';
require '../config/db.php';

$page_title = "Manage Categories";

/* ======================
   ADD CATEGORY
====================== */
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $icon = trim($_POST['icon']);

    if (!empty($name)) {

        // Check duplicate
        $check = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $check->execute([$name]);

        if ($check->rowCount() > 0) {
            $error = "Category already exists!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, icon, status) VALUES (?, ?, 1)");
            $stmt->execute([$name, $icon]);

            header("Location: categories.php?success=1");
            exit();
        }
    } else {
        $error = "Category name is required!";
    }
}


/* ======================
   DELETE CATEGORY
====================== */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: categories.php");
    exit();
}

/* ======================
   TOGGLE STATUS
====================== */
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];

    $stmt = $pdo->prepare("UPDATE categories 
                           SET status = IF(status = 1, 0, 1) 
                           WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: manage_categories.php");
    exit();
}


/* ======================
   FETCH ALL CATEGORIES
====================== */
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();

require 'includes/header.php';
?>
<style>
    /* Container & Layout */
    .manage-container {
        padding: 10px 5px;
    }

    /* Form UI - Refined */
    .add-form {
        background: white;
        padding: 30px;
        border-radius: 20px;
        margin-top: 25px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .form-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 15px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .add-form form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }

    input {
        padding: 12px 16px;
        border-radius: 12px;
        border: 2px solid #f1f5f9;
        background: #f8fafc;
        font-family: inherit;
        font-weight: 500;
        width: 280px;
        transition: all 0.2s;
    }

    input:focus {
        outline: none;
        border-color: #6366f1;
        background: white;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    /* Table UI - Premium Look */
    .table-container {
        background: white;
        padding: 0; /* Changed to 0 to let header span full width */
        border-radius: 20px;
        margin-top: 30px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    table {
        width: 100%;
        border-collapse: collapse;
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
        padding: 18px 25px;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 500;
    }

    tr:last-child td { border-bottom: none; }
    
    tr:hover td { background-color: #fbfcfe; }

    /* Icons & Badges */
    .icon-wrapper {
        width: 40px;
        height: 40px;
        background: #eef2ff;
        color: #6366f1;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .id-badge {
        font-family: monospace;
        color: #94a3b8;
        font-weight: 700;
    }

    /* Buttons */
    button {
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        background: #6366f1;
        color: white;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    button:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .delete-btn {
        background: #fff1f2;
        color: #ef4444;
        padding: 8px 16px;
        font-size: 0.85rem;
    }

    .delete-btn:hover {
        background: #ef4444;
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    .date-text {
        color: #94a3b8;
        font-size: 0.85rem;
    }
</style>

<div class="manage-container">
    <h2 style="font-weight:800; letter-spacing: -1px; margin:0;">Manage Categories</h2>
    <p style="color: #64748b; margin-top: 5px;">Create and organize service sectors for your marketplace.</p>

    <div class="add-form">
        <span class="form-title">Create New Category</span>

        <?php if (isset($_GET['success'])): ?>
    <div style="background:#ecfdf5;color:#10b981;padding:12px 18px;border-radius:12px;margin-top:15px;">
        Category added successfully 🎉
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div style="background:#fff1f2;color:#ef4444;padding:12px 18px;border-radius:12px;margin-top:15px;">
        <?= $error ?>
    </div>
<?php endif; ?>


        <form method="POST">
    <input type="text" name="name" placeholder="e.g. Home Cleaning" required>
    <input type="text" name="icon" placeholder="Bootstrap Icon (e.g. bi-house)">
    <button type="submit" name="add_category">
        <i class="bi bi-plus-lg"></i> Add Category
    </button>
</form>

    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Category Name</th>
                    <th>Date Created</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($categories as $cat): ?>
    <tr style="transition: all 0.2s; border-bottom: 1px solid #f1f5f9;">

        <td style="padding: 18px 25px;">
            <?php if ($cat['status'] == 1): ?>
                <span style="background: #ecfdf5; color: #10b981; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; background: #10b981; border-radius: 50%;"></span> Active
                </span>
            <?php else: ?>
                <span style="background: #fef2f2; color: #ef4444; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; background: #ef4444; border-radius: 50%;"></span> Inactive
                </span>
            <?php endif; ?>
        </td>

        <td style="padding: 18px 25px;">
            <span class="id-badge" style="font-family: monospace; color: #94a3b8; font-weight: 700;">#<?= $cat['id'] ?></span>
        </td>

        <td style="padding: 18px 25px;">
            <div class="icon-wrapper" style="width: 40px; height: 40px; background: #f1f5f9; color: #6366f1; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                <?php if (!empty($cat['icon'])): ?>
                    <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                <?php else: ?>
                    <i class="bi bi-tag"></i>
                <?php endif; ?>
            </div>
        </td>

        <td style="padding: 18px 25px; font-weight: 700; color: #1e293b; font-size: 0.95rem;">
            <?= htmlspecialchars($cat['name']) ?>
        </td>

        <td style="padding: 18px 25px;">
            <span class="date-text" style="color: #94a3b8; font-size: 0.85rem;">
                <?= date('M d, Y', strtotime($cat['created_at'])) ?>
            </span>
        </td>

        <td style="padding: 18px 25px; text-align: right; white-space: nowrap;">
            <a href="?toggle=<?= $cat['id'] ?>" style="text-decoration: none;">
                <button style="background: #fff7ed; color: #f59e0b; border: 1px solid #ffedd5; padding: 8px 14px; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: 0.2s; margin-right: 4px;">
                    <i class="bi bi-arrow-repeat"></i> Toggle
                </button>
            </a>

            <a href="?delete=<?= $cat['id'] ?>" 
               onclick="return confirm('Delete this category?')" style="text-decoration: none;">
                <button class="delete-btn" style="background: #fff1f2; color: #ef4444; border: 1px solid #fee2e2; padding: 8px 14px; border-radius: 10px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: 0.2s;">
                    <i class="bi bi-trash3"></i> Delete
                </button>
            </a>
        </td>

    </tr>
    <?php endforeach; ?>
</tbody>
        </table>
    </div>
</div>

<?php require 'includes/footer.php'; ?>