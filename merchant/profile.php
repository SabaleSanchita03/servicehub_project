<?php
require "../includes/auth_check.php";
require "../config/db.php";

$user_id = $_SESSION['user_id'];

// 1. Get Merchant ID and User Name from core tables
$stmt = $pdo->prepare("SELECT m.id as merchant_id, u.name, u.email FROM merchants m JOIN users u ON m.user_id = u.id WHERE m.user_id = ?");
$stmt->execute([$user_id]);
$core = $stmt->fetch();
$merchant_id = $core['merchant_id'];

// 2. Fetch or Create Profile from the NEW table (merchant_profiles)
$profileStmt = $pdo->prepare("SELECT * FROM merchant_profiles WHERE merchant_id = ?");
$profileStmt->execute([$merchant_id]);
$profile = $profileStmt->fetch();

// If profile doesn't exist yet, create an empty one
if (!$profile) {
    $ins = $pdo->prepare("INSERT INTO merchant_profiles (merchant_id) VALUES (?)");
    $ins->execute([$merchant_id]);
    $profile = ['phone' => '', 'address' => '', 'description' => '', 'skills' => ''];
}

// 3. Update Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name']; // Updates Users table
    $phone = $_POST['phone']; // Updates Profiles table
    $address = $_POST['address'];
    $description = $_POST['description'];

    // Update User Name
    $updUser = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
    $updUser->execute([$name, $user_id]);

    // Update the NEW Profile Table
    $updProf = $pdo->prepare("UPDATE merchant_profiles SET phone = ?, address = ?, description = ? WHERE merchant_id = ?");
    $updProf->execute([$phone, $address, $description, $merchant_id]);

    header("Location: profile.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Merchant Profile | ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --primary: #6366f1; --dark: #0f172a; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }
        .sidebar { width: 280px; height: 100vh; background: var(--dark); position: fixed; padding: 2rem; color: white; }
        .content { margin-left: 280px; padding: 3rem; }
        .profile-card { background: white; border-radius: 20px; border: 1px solid #e2e8f0; padding: 2.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .form-label { font-weight: 700; color: #64748b; font-size: 0.8rem; text-transform: uppercase; }
        .form-control { border-radius: 10px; padding: 0.75rem; border: 1px solid #cbd5e1; }
        .nav-link { color: #94a3b8; text-decoration: none; display: block; padding: 0.8rem; border-radius: 10px; margin-bottom: 0.5rem; }
        .nav-link.active { background: var(--primary); color: white; }
    </style>
</head>
<body>

<aside class="sidebar">
    <h3 class="fw-800 mb-5">Service<span>Hub</span></h3>
    <a href="das.php" class="nav-link"><i class="bi bi-grid me-2"></i> Dashboard</a>
    <a href="profile.php" class="nav-link active"><i class="bi bi-person me-2"></i> Profile Settings</a>
    <a href="../auth/logout.php" class="nav-link text-danger mt-auto"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</aside>

<main class="content">
    <div class="mb-4">
        <h2 class="fw-800">Account Settings</h2>
        <p class="text-muted">Manage your personal details and public profile.</p>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4">Profile updated successfully!</div>
    <?php endif; ?>

    <div class="profile-card">
        <form method="POST">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($core['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email (Private)</label>
                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($core['email']) ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($profile['phone']) ?>" placeholder="Public contact number">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($profile['address']) ?>" placeholder="City, State">
                </div>
                <div class="col-12">
                    <label class="form-label">Professional Bio</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Tell customers about your expertise..."><?= htmlspecialchars($profile['description']) ?></textarea>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" name="update_profile" class="btn btn-primary px-5 py-2 fw-bold">Save Profile Changes</button>
                </div>
            </div>
        </form>
    </div>
</main>

</body>
</html>