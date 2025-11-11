<?php
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(SITE_URL);
}

$page_title = 'Reset Password';
$token = sanitize($_GET['token'] ?? '');
$success = '';
$error = '';
$valid_token = false;

if (empty($token)) {
    redirect(SITE_URL . '/auth/forgot-password.php');
}

// Verify token
$pdo = getPDOConnection();
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND user_type = 'user' AND expires_at > NOW()");
$stmt->execute([$token]);
$reset_request = $stmt->fetch();

if (!$reset_request) {
    $error = 'Invalid or expired reset link. Please request a new one.';
} else {
    $valid_token = true;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirm_password)) {
            $error = 'Please fill in all fields';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            
            if ($stmt->execute([$hashed_password, $reset_request['email']])) {
                // Delete used token
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                
                $success = 'Password reset successfully! You can now login with your new password.';
                $valid_token = false;
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
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

    .btn-auth {
        padding: 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        width: 100%;
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
            <i class="fas fa-lock fa-3x mb-3" style="color: var(--primary-color);"></i>
            <h2>Reset Password</h2>
            <p class="text-secondary mb-0">Enter your new password</p>
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
        <div class="text-center mt-3">
            <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-primary btn-auth">
                <i class="fas fa-sign-in-alt me-2"></i>Go to Login
            </a>
        </div>
        <?php endif; ?>

        <?php if ($valid_token && !$success): ?>
        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter new password" required minlength="6">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="toggleIcon1"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Password must be at least 6 characters long.
                </div>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm new password" required minlength="6">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye" id="toggleIcon2"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Please confirm your password.
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-auth">
                <i class="fas fa-check me-2"></i>Reset Password
            </button>
        </form>
        <?php endif; ?>

        <?php if (!$valid_token && !$success): ?>
        <div class="text-center">
            <a href="<?php echo SITE_URL; ?>/auth/forgot-password.php" class="btn btn-primary btn-auth">
                <i class="fas fa-redo me-2"></i>Request New Link
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId === 'password' ? 'toggleIcon1' : 'toggleIcon2');
    
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
