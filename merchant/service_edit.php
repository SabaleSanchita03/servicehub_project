<?php
require "../includes/auth_check.php";
require "../config/db.php";

$user_id = $_SESSION['user_id'];
$service_id = $_GET['id'] ?? 0;

// Get merchant_id
$stmt = $pdo->prepare("SELECT id FROM merchants WHERE user_id=?");
$stmt->execute([$user_id]);
$merchant_id = $stmt->fetchColumn();

// Fetch service
$stmt = $pdo->prepare("SELECT * FROM services WHERE id=? AND merchant_id=?");
$stmt->execute([$service_id, $merchant_id]);
$service = $stmt->fetch();

if(!$service){
    die("Service not found or unauthorized");
}

if(isset($_POST['update'])){

    $imageName = $service['image']; // keep old image by default

    if(!empty($_FILES['image']['name'])){

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','jfif'];

        if(!in_array($ext,$allowed)){
            die("Invalid image format");
        }

        if($ext === 'jfif'){ $ext = 'jpg'; }

        if($_FILES['image']['size'] > 2*1024*1024){
            die("Image too large");
        }

        if(!getimagesize($_FILES['image']['tmp_name'])){
            die("Invalid image file");
        }

        // delete old image
        if($service['image'] && file_exists("../assets/services/".$service['image'])){
            unlink("../assets/services/".$service['image']);
        }

        $imageName = time().'_'.uniqid().'.'.$ext;
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            "../assets/services/".$imageName
        );
    }

    $stmt = $pdo->prepare("
        UPDATE services SET 
        title=?, description=?, price=?, duration=?, image=?
        WHERE id=? AND merchant_id=?
    ");

    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['price'],
        $_POST['duration'],
        $imageName,
        $service_id,
        $merchant_id
    ]);

    header("Location: services.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service – ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brand-color: #6366f1;
            --brand-hover: #4f46e5;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: #1e293b;
        }

        .form-container {
            max-width: 650px;
            margin: 3rem auto;
        }

        .card-custom {
            background: #fff;
            border: none;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.04);
            padding: 2.5rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #fdfdfd;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--brand-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background-color: #fff;
            outline: none;
        }

        /* Image Preview Section */
        .image-preview-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }

        .current-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-update {
            background-color: var(--brand-color);
            border: none;
            border-radius: 12px;
            padding: 0.9rem;
            font-weight: 700;
            transition: all 0.2s;
        }

        .btn-update:hover {
            background-color: var(--brand-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .back-btn {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-btn:hover {
            color: var(--brand-color);
        }

        .input-group-text {
            background: #f1f5f9;
            border-color: #e2e8f0;
            border-radius: 12px 0 0 12px !important;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="form-container">
        
        <div class="mb-4">
            <a href="services.php" class="back-btn mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Back to Services
            </a>
            <h2 class="fw-bold mb-0 text-dark">Edit Service Details</h2>
            <p class="text-muted small">Update your service information and pricing.</p>
        </div>

        <div class="card-custom">
            <form method="POST" enctype="multipart/form-data">

                <div class="mb-4">
                    <label class="form-label">Service Title</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?= htmlspecialchars($service['title']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Detailed Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($service['description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" name="price" class="form-control" 
                                   value="<?= $service['price'] ?>" style="border-radius: 0 12px 12px 0 !important;" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Duration</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            <input type="text" name="duration" class="form-control" 
                                   value="<?= $service['duration'] ?>" style="border-radius: 0 12px 12px 0 !important;">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Current Service Image</label>
                    <div class="image-preview-wrapper mb-3">
                        <img src="../assets/services/<?= $service['image'] ?? 'default.png' ?>" class="current-img" alt="Service">
                        <div>
                            <p class="mb-1 fw-semibold small">Current Cover</p>
                            <p class="text-muted small mb-0">This image is currently visible to customers.</p>
                        </div>
                    </div>
                    
                    <label class="form-label">Replace with New Image</label>
                    <input type="file" name="image" class="form-control">
                    <div class="form-text">Leave blank if you don't want to change the image.</div>
                </div>

                <button name="update" type="submit" class="btn btn-primary btn-update w-100">
                    <i class="bi bi-arrow-repeat me-2"></i> Save Changes
                </button>
            </form>
        </div>

    </div>
</div>
</body>
</html>