<?php
require "../includes/auth_check.php";
require "../config/db.php";

/* ===============================
    Fetch Categories
================================ */
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

/* ===============================
    Get Merchant ID
================================ */
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT id FROM merchants WHERE user_id = ?");
$stmt->execute([$user_id]);
$merchant_id = $stmt->fetchColumn();

if (!$merchant_id) { die("Merchant not found."); }

/* ===============================
    Handle Form Submit
================================ */
if (isset($_POST['save'])) {
    if (empty($_POST['category_id'])) { die("Please select a service category."); }
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $filename = trim($_FILES['image']['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','jfif'];
        if (!in_array($ext, $allowed)) { die("Invalid format."); }
        
        $imageName = time() . '_' . uniqid() . '.' . ($ext === 'jfif' ? 'jpg' : $ext);
        $uploadDir = "../assets/services/";
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
    }

    $stmt = $pdo->prepare("
    INSERT INTO services 
    (merchant_id, category_id, location, title, description, image, price, duration) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $merchant_id,
    $_POST['category_id'],
    $_POST['location'],
    $_POST['title'],
    $_POST['description'],
    $imageName,
    $_POST['price'],
    $_POST['duration']
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
    <title>Create Service – ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root { --primary: #6366f1; --primary-hover: #4f46e5; --bg: #f8fafc; --text-main: #1e293b; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg); color: var(--text-main); }
        
        .form-card { 
            background: #fff; border-radius: 24px; border: none; 
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 10px 10px -5px rgba(0,0,0,0.02);
        }

        .form-label { font-weight: 600; font-size: 0.85rem; color: #64748b; margin-bottom: 0.6rem; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .form-control, .form-select { 
            border-radius: 12px; padding: 0.75rem 1rem; border: 1px solid #e2e8f0; 
            background-color: #fdfdfd; transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus { 
            border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); 
            background-color: #fff;
        }

        /* Image Preview Style */
        .preview-box {
            width: 100%; height: 200px; border-radius: 16px; border: 2px dashed #cbd5e1;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            overflow: hidden; background: #f8fafc; position: relative; transition: 0.3s;
        }
        .preview-box:hover { border-color: var(--primary); background: #f5f7ff; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; display: none; }
        .upload-placeholder { pointer-events: none; text-align: center; }

        .btn-primary { 
            background: var(--primary); border: none; border-radius: 12px; 
            padding: 0.8rem; font-weight: 600; transition: 0.3s;
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
        
        .back-link { text-decoration: none; color: #64748b; font-weight: 500; font-size: 0.9rem; transition: 0.2s; }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="services.php" class="back-link"><i class="bi bi-arrow-left me-1"></i> Back to Services</a>
                <span class="badge bg-white text-muted border px-3 py-2 rounded-pill">New Service</span>
            </div>

            <div class="form-card p-4 p-md-5">
                <h3 class="fw-bold mb-4">Service Details</h3>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="mb-4">
                                <label class="form-label">Service Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Professional Wedding DJ" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
    <label class="form-label">Service Location</label>
    <input 
        type="text" 
        name="location" 
        class="form-control" 
        placeholder="e.g. Mumbai, Pune"
        required
    >
</div>


                            
                            <div class="mb-4">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Tell customers what's included..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="mb-4">
                                <label class="form-label">Cover Image</label>
                                <label for="file-upload" class="preview-box" id="drop-area">
                                    <div class="upload-placeholder" id="placeholder">
                                        <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                                        <p class="mb-0 small fw-medium">Upload Image</p>
                                    </div>
                                    <img id="image-preview" src="#" alt="Preview">
                                    <input type="file" id="file-upload" name="image" accept="image/*" hidden>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Price (₹)</label>
                                <input type="number" name="price" class="form-control" placeholder="0.00" required>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Duration</label>
                                <input type="text" name="duration" class="form-control" placeholder="e.g. 4 Hours / Per Day">
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <hr class="text-muted opacity-25 mb-4">
                            <div class="d-grid">
                                <button type="submit" name="save" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Publish Service
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    const fileUpload = document.getElementById('file-upload');
    const imagePreview = document.getElementById('image-preview');
    const placeholder = document.getElementById('placeholder');

    fileUpload.onchange = evt => {
        const [file] = fileUpload.files;
        if (file) {
            imagePreview.src = URL.createObjectURL(file);
            imagePreview.style.display = 'block';
            placeholder.style.display = 'none';
        }
    }
</script>

</body>
</html>