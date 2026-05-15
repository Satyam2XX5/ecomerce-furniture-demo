<?php
require_once 'includes/db.php';

// Agar pehle se login hai toh redirect karo
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: my-orders.php");
    exit();
}

$errors = [];
$success = '';
$page_title = "Register";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($conn, $_POST['name'] ?? '');
    $email    = sanitize($conn, $_POST['email'] ?? '');
    $phone    = sanitize($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name))                   $errors[] = "Naam zaroori hai";
    if (empty($email))                  $errors[] = "Email zaroori hai";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email daalo";
    if (strlen($password) < 6)          $errors[] = "Password kam se kam 6 characters ka hona chahiye";
    if ($password !== $confirm)         $errors[] = "Passwords match nahi kar rahe";

    if (empty($errors)) {
        // Check email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "Ye email already registered hai. Login karo.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $_SESSION['user_id']   = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                // Welcome email
                $admin_email = mysqli_fetch_assoc(db_query($conn, "SELECT setting_value FROM settings WHERE setting_key='admin_email'"))['setting_value'] ?? 'Santoshfurniture@gmail.com';

                $to = $email;
                $subject = "Santosh Furniture mein aapka swagat hai!";
                $body = "
                <html><body style='font-family:Arial,sans-serif; max-width:600px; margin:0 auto;'>
                <div style='background:#8B4513; padding:30px; text-align:center;'>
                    <h1 style='color:white; margin:0;'>🪑 Santosh Furniture</h1>
                </div>
                <div style='padding:30px; background:#fff;'>
                    <h2 style='color:#8B4513;'>Namaskar $name ji! 🙏</h2>
                    <p>Aapka Santosh Furniture mein registration safal raha!</p>
                    <p>Ab aap apne orders track kar sakte hain aur apne account se quotes submit kar sakte hain.</p>
                    <div style='background:#FFF8F0; padding:20px; border-radius:10px; margin:20px 0;'>
                        <p><strong>Aapke account ki jankari:</strong></p>
                        <p>📧 Email: $email</p>
                        <p>📞 Phone: $phone</p>
                    </div>
                    <a href='http://".$_SERVER['HTTP_HOST']."/furniture-website/my-orders.php' 
                       style='background:#8B4513; color:white; padding:12px 30px; 
                              text-decoration:none; border-radius:8px; display:inline-block;'>
                        Mera Dashboard Dekho
                    </a>
                    <p style='margin-top:30px; color:#666;'>Koi sawaal ho toh call karein: <strong>+91 8210187952</strong></p>
                </div>
                </body></html>";

                $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: Santosh Furniture <$admin_email>\r\n";
                @mail($to, $subject, $body, $headers);

                $redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'my-orders.php?welcome=1';
                // Safety check — only allow relative paths or same-domain full URLs
                if (!filter_var($redirect, FILTER_VALIDATE_URL)) {
                    $redirect = 'my-orders.php?welcome=1';
                }
                header("Location: " . $redirect);
                exit();
            } else {
                $errors[] = "Registration fail ho gaya. Dobara try karo.";
            }
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
                <li class="breadcrumb-item active">Register</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card border-0 shadow-lg" style="border-radius:20px; overflow:hidden;">
                <div style="background:linear-gradient(135deg,#8B4513,#A0522D); padding:35px; text-align:center;">
                    <h2 style="color:white; font-family:'Playfair Display',serif; margin:0;">
                        <i class="fas fa-user-plus me-2"></i>Account Banao
                    </h2>
                    <p style="color:rgba(255,255,255,0.8); margin:8px 0 0;">Santosh Furniture ka hissa bano!</p>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" style="border-radius:10px;">
                        <?php foreach ($errors as $e): ?>
                            <div><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? 'my-orders.php?welcome=1') ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Poora Naam *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="name" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                       placeholder="Aapka naam" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="aapka@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       placeholder="+91 98765 43210">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" id="pw1" class="form-control"
                                       placeholder="Kam se kam 6 characters" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePw('pw1',this)"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password Confirm karo *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="confirm_password" id="pw2" class="form-control"
                                       placeholder="Password dobara likhein" required>
                                <button type="button" class="btn btn-outline-secondary" 
                                        onclick="togglePw('pw2',this)"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 fw-bold"
                                style="background:linear-gradient(135deg,#8B4513,#A0522D); 
                                       color:white; border-radius:12px; font-size:16px;">
                            <i class="fas fa-user-plus me-2"></i>Account Banao
                        </button>
                    </form>

                    <hr class="my-4">
                    <p class="text-center mb-0">
                        Pehle se account hai? 
                        <a href="login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>" style="color:#8B4513; font-weight:600;">Login karo</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const inp = document.getElementById(id);
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
