<?php
require "../config/db.php";

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // 1. Check if email already exists
    $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->execute([$email]);

    if ($checkEmail->rowCount() > 0) {
        $error = "This email is already registered. Please try logging in.";
    } else {
        try {
            $pdo->beginTransaction();

            // 2. Insert into users table
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role]);
            $user_id = $pdo->lastInsertId();

            // 3. If merchant, create a blank profile so the dashboard doesn't crash
         if ($role === 'merchant') {
    $stmtM = $pdo->prepare("INSERT INTO merchants (user_id, merchant_name) VALUES (?, ?)");
    $stmtM->execute([$user_id, $name]);
}

            $pdo->commit();
            header("Location: login.php?msg=registered");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register – ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root { 
            --brand-color: #6366f1; 
            --brand-hover: #4338ca; 
            --bg-light: #f8fafc; 
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-light); 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center; 
            margin: 0; 
        }
        
        /* --- ServiceHub Master Logo Design --- */
        .brand-logo {
            display: flex;
            align-items: center;
            text-decoration: none !important;
            gap: 12px;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .brand-icon {
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .brand-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.04em;
            color: #1e293b;
            margin: 0;
        }

        .brand-text span {
            color: #6366f1;
        }

        .register-card { 
            background: #ffffff; 
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 24px; 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05); 
            width: 100%; 
            max-width: 420px; 
            padding: 2.5rem; 
        }

        .form-control, .form-select { 
            border-radius: 12px; 
            padding: 0.75rem 1rem; 
            border: 1px solid #e2e8f0; 
            background-color: #fdfdfd; 
            margin-bottom: 1.25rem; 
            transition: all 0.2s; 
        }

        .form-control:focus, .form-select:focus { 
            border-color: var(--brand-color); 
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); 
            background-color: #fff; 
            outline: none; 
        }

        .btn-primary { 
            background-color: var(--brand-color); 
            border: none; 
            border-radius: 12px; 
            padding: 0.8rem; 
            font-weight: 600; 
            width: 100%; 
            transition: 0.2s; 
        }

        .btn-primary:hover { 
            background-color: var(--brand-hover); 
            transform: translateY(-1px); 
        }
    </style>
</head>
<body>

<div class="container-fluid d-flex flex-column align-items-center py-5">
    <a href="../index.php" class="brand-logo">
        <div class="brand-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <h1 class="brand-text">Service<span>Hub</span></h1>
    </a>
    
    <div class="register-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-dark">Create Account 🚀</h3>
            <p class="text-muted small">Pick a role and start today.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger border-0" style="border-radius: 12px; font-size: 0.85rem; background-color: #fef2f2; color: #991b1b;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="fw-semibold small mb-1 text-secondary">Full Name</label>
            <input class="form-control" name="name" placeholder="John Doe" required>

            <label class="fw-semibold small mb-1 text-secondary">Email Address</label>
            <input class="form-control" name="email" type="email" placeholder="name@example.com" required>

            <label class="fw-semibold small mb-1 text-secondary">Password</label>
            <input class="form-control" name="password" type="password" placeholder="••••••••" required>

            <label class="fw-semibold small mb-1 text-secondary">Register as</label>
            <select name="role" class="form-select" required>
                <option value="">Select your role</option>
                <option value="customer">I want to hire</option>
                <option value="merchant">I want to provide services</option>
            </select>

            <button name="register" class="btn btn-primary mt-2 shadow-sm">Sign Up</button>
        </form>

        <div class="text-center mt-4 small text-muted">
            Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Log in</a>
        </div>
    </div>
</div>

</body>
</html>