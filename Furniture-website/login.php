<?php
require_once 'includes/db.php';

session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: my-orders.php");
    exit();
}

$errors = [];
$page_title = "Login";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Email aur password zaroori hain";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_active']) {
                $errors[] = "Aapka account inactive hai. Admin se contact karein.";
            } else {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                $redirect = $_GET['redirect'] ?? 'my-orders.php';
                header("Location: " . htmlspecialchars($redirect));
                exit();
            }
        } else {
            $errors[] = "Email ya password galat hai";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Login</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <div class="card border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
                <div style="background:linear-gradient(135deg,#8B4513,#A0522D); padding:35px; text-align:center;">
                    <h2 style="color:white; font-family:'Playfair Display',serif; margin:0;">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </h2>
                    <p style="color:rgba(255,255,255,0.8); margin:8px 0 0;">Apne account mein aao</p>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" style="border-radius:10px;">
                        <?php foreach ($errors as $e): ?>
                            <div><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-info" style="border-radius:10px;">
                        <i class="fas fa-info-circle me-1"></i><?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php?redirect=<?= urlencode($_GET['redirect'] ?? 'my-orders.php') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="aapka@email.com" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" id="pw" class="form-control"
                                       placeholder="Aapka password" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePw()"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 fw-bold"
                                style="background:linear-gradient(135deg,#8B4513,#A0522D); 
                                       color:white; border-radius:12px; font-size:16px;">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Karo
                        </button>
                    </form>

                    <hr class="my-4">
                    <p class="text-center mb-0">
                        Naya account chahiye? 
                        <a href="register.php" style="color:#8B4513; font-weight:600;">Register karo</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function togglePw() {
    const inp = document.getElementById('pw');
    const btn = inp.nextElementSibling;
    if (inp.type === 'password') {
        inp.type = 'text';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        inp.type = 'password';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
