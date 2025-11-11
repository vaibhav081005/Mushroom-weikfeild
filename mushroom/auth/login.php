<?php
require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL);
}

$page_title = 'Login';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_blocked']) {
                $error = 'Your account has been blocked. Please contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to intended page or home
                $redirect_to = $_GET['redirect'] ?? SITE_URL;
                redirect($redirect_to);
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .auth-card {
        max-width: 450px;
        width: 100%;
        background: var(--surface-color);
        border-radius: 16px;
        box-shadow: var(--shadow-hover);
        padding: 2.5rem;
    }

    .auth-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .auth-header h2 {
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .form-control {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 2px solid var(--border-color);
    }

    .form-control:focus {
        border-color: var(--primary-color);
    }

    .btn-auth {
        padding: 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        width: 100%;
    }

    .divider {
        text-align: center;
        margin: 1.5rem 0;
        position: relative;
    }

    .divider::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        width: 100%;
        height: 1px;
        background: var(--border-color);
    }

    .divider span {
        background: var(--surface-color);
        padding: 0 1rem;
        position: relative;
        color: var(--text-secondary);
    }

    @media (max-width: 768px) {
        .auth-card {
            padding: 1.5rem;
            margin: 0 1rem;
        }
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-leaf fa-3x mb-3" style="color: var(--primary-color);"></i>
            <h2>Welcome Back</h2>
            <p class="text-secondary mb-0">Login to your account</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Enter your email" required
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Please enter your password.
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
                <a href="<?php echo SITE_URL; ?>/auth/forgot-password.php" class="text-decoration-none">
                    Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn btn-primary btn-auth">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>

        <div class="divider">
            <span>OR</span>
        </div>

        <div class="text-center">
            <p class="mb-0">Don't have an account? 
                <a href="<?php echo SITE_URL; ?>/auth/signup.php" class="fw-bold text-decoration-none">
                    Sign Up
                </a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
