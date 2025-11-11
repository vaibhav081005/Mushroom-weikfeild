<?php
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(SITE_URL);
}

$page_title = 'Forgot Password';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete old tokens
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ? AND user_type = 'user'");
            $stmt->execute([$email]);
            
            // Insert new token
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, user_type, expires_at) VALUES (?, ?, 'user', ?)");
            $stmt->execute([$email, $token, $expires_at]);
            
            // Send reset email
            $reset_link = SITE_URL . '/auth/reset-password.php?token=' . $token;
            $subject = 'Password Reset Request';
            $message = "
                <h2>Password Reset Request</h2>
                <p>Hi {$user['name']},</p>
                <p>We received a request to reset your password. Click the link below to reset it:</p>
                <p><a href='$reset_link'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
            ";
            
            sendEmail($email, $subject, $message);
            
            $success = 'Password reset link has been sent to your email address.';
        } else {
            // Don't reveal if email exists or not for security
            $success = 'If an account exists with this email, a password reset link has been sent.';
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
            <i class="fas fa-key fa-3x mb-3" style="color: var(--primary-color);"></i>
            <h2>Forgot Password?</h2>
            <p class="text-secondary mb-0">Enter your email to reset your password</p>
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
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Enter your email" required
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-auth mb-3">
                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
            </button>

            <div class="text-center">
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
