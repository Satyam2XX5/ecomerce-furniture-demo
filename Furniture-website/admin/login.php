<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Already logged in toh dashboard pe bhejo
if (isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email aur password dono bharo!";
    } else {
        $result = db_query($conn, "SELECT * FROM admin_users WHERE email = '$email' LIMIT 1");
        $admin  = mysqli_fetch_assoc($result);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email']= $admin['email'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Email ya password galat hai!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | WoodCraft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="admin-login-page">
    <div class="admin-login-card">

        <!-- Logo -->
        <div class="text-center mb-4">
            <div style="width:64px; height:64px; background:linear-gradient(135deg, var(--primary), var(--primary-dark));
                        border-radius:16px; display:flex; align-items:center; justify-content:center;
                        margin:0 auto 16px; font-size:28px;">
                🪑
            </div>
            <h2>WoodCraft Admin</h2>
            <p class="text-muted" style="font-size:14px;">Apna account se login karo</p>
        </div>

        <!-- Error -->
        <?php if($error): ?>
        <div class="alert alert-danger text-center mb-4" style="border-radius:12px; font-size:14px;">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST">
            <div class="mb-4">
                <label class="form-label fw-600">
                    <i class="fas fa-envelope me-1" style="color:var(--primary);"></i> Email
                </label>
                <input type="email" name="email" class="form-control"
                       placeholder="admin@furniture.com"
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                       required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label fw-600">
                    <i class="fas fa-lock me-1" style="color:var(--primary);"></i> Password
                </label>
                <div class="position-relative">
                    <input type="password" name="password" id="password-input"
                           class="form-control" placeholder="••••••••" required>
                    <button type="button"
                            onclick="togglePassword()"
                            style="position:absolute; right:14px; top:50%; transform:translateY(-50%);
                                   background:none; border:none; color:var(--text-muted); cursor:pointer;">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary-wood w-100"
                    style="padding:14px; font-size:16px; border-radius:12px;">
                <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
        </form>

        <p class="text-center text-muted mt-4 mb-0" style="font-size:13px;">
            <a href="../index.php" style="color:var(--primary);">
                <i class="fas fa-arrow-left me-1"></i> Visitor website pe jao
            </a>
        </p>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password-input');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
</body>
</html>