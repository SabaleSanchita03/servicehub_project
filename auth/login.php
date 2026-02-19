<?php
session_start();
require "../config/db.php";


// Get redirect URL from GET or POST
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';


if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // Role-based Handling
        // Role-based Handling
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] === 'merchant') {
            header("Location: ../merchant/dashboard.php");
        } else {
            // CUSTOMER FLOW: redirect to previous page if set
            if (!empty($redirect)) {
                header("Location: $redirect");
            } else {
                header("Location: ../index.php");
            }
        }
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – ServiceHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --brand-color: #6366f1;
            --brand-hover: #4338ca;
            --bg-light: #f9fafb;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            height: 100vh;
            display: flex;
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
            background: linear-gradient(135deg, var(--brand-color) 0%, var(--brand-hover) 100%);
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
            color: var(--brand-color);
        }

        /* --- Card & Form Styles --- */
        .card {
            border: none;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem 2rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            background-color: #fdfdfd;
        }

        .form-control:focus {
            border-color: var(--brand-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-primary {
            background-color: var(--brand-color);
            border: none;
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--brand-hover);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="login-container px-3">
    <a href="../index.php" class="brand-logo">
        <div class="brand-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <h1 class="brand-text">Service<span>Hub</span></h1>
    </a>
    
    <div class="card bg-white">
        <h4 class="fw-bold text-center mb-1">Welcome Back</h4>
        <p class="text-muted text-center small mb-4">Access your dashboard</p>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
            <div class="alert alert-success border-0 small py-2 mb-4 text-center">
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger border-0 small py-2 mb-4 text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>
<form method="POST">
    <?php if(!empty($redirect)): ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label small fw-semibold">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
    </div>

    <div class="mb-4">
        <label class="form-label small fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>

    <button name="login" type="submit" class="btn btn-primary w-100">Sign In</button>
</form>


        <div class="text-center mt-4 small text-muted">
            New here? <a href="register.php" class="text-primary fw-bold text-decoration-none">Create an account</a>
        </div>
    </div>
</div>

</body>
</html>