<?php
require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL);
}

$page_title = 'Sign Up';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $pdo = getPDOConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email address is already registered';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $hashed_password])) {
                $user_id = $pdo->lastInsertId();
                
                // Auto login
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                // Send welcome email
                $subject = 'Welcome to ' . SITE_NAME;
                $message = "
                    <h2>Welcome to " . SITE_NAME . "!</h2>
                    <p>Hi $name,</p>
                    <p>Thank you for signing up. We're excited to have you on board!</p>
                    <p>You can now browse and purchase our premium mushroom products.</p>
                    <p>Best regards,<br>The " . SITE_NAME . " Team</p>
                ";
                sendEmail($email, $subject, $message);
                
                redirect(SITE_URL);
            } else {
                $error = 'Failed to create account. Please try again.';
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

    .form-control:focus {
        border-color: var(--primary-color);
    }

    .btn-auth {
        padding: 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        width: 100%;
    }

    .password-strength {
        height: 4px;
        border-radius: 2px;
        margin-top: 0.5rem;
        background: var(--border-color);
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        width: 0;
        transition: all 0.3s ease;
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
            <h2>Create Account</h2>
            <p class="text-secondary mb-0">Join us and start shopping</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       placeholder="Enter your full name" required
                       value="<?php echo htmlspecialchars($name ?? ''); ?>">
                <div class="invalid-feedback">
                    Please enter your full name.
                </div>
            </div>

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
                           placeholder="Create a password" required minlength="6"
                           oninput="checkPasswordStrength()">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="toggleIcon1"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <small class="text-secondary" id="strengthText">Password strength</small>
                <div class="invalid-feedback">
                    Password must be at least 6 characters long.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye" id="toggleIcon2"></i>
                    </button>
                </div>
                <div class="invalid-feedback">
                    Please confirm your password.
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                </label>
                <div class="invalid-feedback">
                    You must agree to the terms and conditions.
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-auth">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
        </form>

        <div class="divider">
            <span>OR</span>
        </div>

        <div class="text-center">
            <p class="mb-0">Already have an account? 
                <a href="<?php echo SITE_URL; ?>/auth/login.php" class="fw-bold text-decoration-none">
                    Login
                </a>
            </p>
        </div>
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

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    const colors = ['#dc3545', '#ffc107', '#17a2b8', '#28a745'];
    const texts = ['Weak', 'Fair', 'Good', 'Strong'];
    const widths = ['25%', '50%', '75%', '100%'];
    
    if (password.length === 0) {
        strengthBar.style.width = '0';
        strengthText.textContent = 'Password strength';
        strengthText.style.color = 'var(--text-secondary)';
    } else {
        const index = Math.min(strength - 1, 3);
        strengthBar.style.width = widths[index];
        strengthBar.style.backgroundColor = colors[index];
        strengthText.textContent = texts[index];
        strengthText.style.color = colors[index];
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
