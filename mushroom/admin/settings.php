<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Settings';
$success = '';
$error = '';

$pdo = getPDOConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => sanitize($_POST['site_name'] ?? ''),
        'site_email' => sanitize($_POST['site_email'] ?? ''),
        'site_phone' => sanitize($_POST['site_phone'] ?? ''),
        'currency' => sanitize($_POST['currency'] ?? 'INR'),
        'currency_symbol' => sanitize($_POST['currency_symbol'] ?? '₹'),
        'tax_percentage' => floatval($_POST['tax_percentage'] ?? 18),
        'payment_gateway' => sanitize($_POST['payment_gateway'] ?? 'razorpay'),
        'razorpay_key_id' => sanitize($_POST['razorpay_key_id'] ?? ''),
        'razorpay_key_secret' => sanitize($_POST['razorpay_key_secret'] ?? ''),
        'stripe_public_key' => sanitize($_POST['stripe_public_key'] ?? ''),
        'stripe_secret_key' => sanitize($_POST['stripe_secret_key'] ?? ''),
        'paypal_client_id' => sanitize($_POST['paypal_client_id'] ?? ''),
        'paypal_secret' => sanitize($_POST['paypal_secret'] ?? ''),
        'download_expiry_days' => intval($_POST['download_expiry_days'] ?? 30),
        'footer_text' => sanitize($_POST['footer_text'] ?? '')
    ];
    
    $all_updated = true;
    foreach ($settings as $key => $value) {
        if (!updateSetting($key, $value)) {
            $all_updated = false;
        }
    }
    
    if ($all_updated) {
        $success = 'Settings updated successfully';
    } else {
        $error = 'Some settings failed to update';
    }
}

// Get current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings_data = [];
while ($row = $stmt->fetch()) {
    $settings_data[$row['setting_key']] = $row['setting_value'];
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .settings-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #2e7d32;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Settings</h2>
            <p class="text-secondary mb-0">Configure your website settings</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- General Settings -->
        <div class="settings-section">
            <h4 class="section-title">
                <i class="fas fa-cog me-2"></i>General Settings
            </h4>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Site Name</label>
                    <input type="text" class="form-control" name="site_name" 
                           value="<?php echo htmlspecialchars($settings_data['site_name'] ?? ''); ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Site Email</label>
                    <input type="email" class="form-control" name="site_email" 
                           value="<?php echo htmlspecialchars($settings_data['site_email'] ?? ''); ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Site Phone</label>
                    <input type="text" class="form-control" name="site_phone" 
                           value="<?php echo htmlspecialchars($settings_data['site_phone'] ?? ''); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Download Expiry (Days)</label>
                    <input type="number" class="form-control" name="download_expiry_days" 
                           value="<?php echo htmlspecialchars($settings_data['download_expiry_days'] ?? 30); ?>" min="1">
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Footer Text</label>
                    <input type="text" class="form-control" name="footer_text" 
                           value="<?php echo htmlspecialchars($settings_data['footer_text'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <!-- Currency Settings -->
        <div class="settings-section">
            <h4 class="section-title">
                <i class="fas fa-money-bill me-2"></i>Currency & Tax Settings
            </h4>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Currency Code</label>
                    <select class="form-select" name="currency">
                        <option value="INR" <?php echo ($settings_data['currency'] ?? '') === 'INR' ? 'selected' : ''; ?>>INR - Indian Rupee</option>
                        <option value="USD" <?php echo ($settings_data['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>USD - US Dollar</option>
                        <option value="EUR" <?php echo ($settings_data['currency'] ?? '') === 'EUR' ? 'selected' : ''; ?>>EUR - Euro</option>
                        <option value="GBP" <?php echo ($settings_data['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>GBP - British Pound</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Currency Symbol</label>
                    <input type="text" class="form-control" name="currency_symbol" 
                           value="<?php echo htmlspecialchars($settings_data['currency_symbol'] ?? '₹'); ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tax Percentage (%)</label>
                    <input type="number" class="form-control" name="tax_percentage" 
                           value="<?php echo htmlspecialchars($settings_data['tax_percentage'] ?? 18); ?>" 
                           min="0" max="100" step="0.01">
                </div>
            </div>
        </div>

        <!-- Payment Gateway Settings -->
        <div class="settings-section">
            <h4 class="section-title">
                <i class="fas fa-credit-card me-2"></i>Payment Gateway Settings
            </h4>

            <div class="mb-4">
                <label class="form-label">Active Payment Gateway</label>
                <select class="form-select" name="payment_gateway">
                    <option value="razorpay" <?php echo ($settings_data['payment_gateway'] ?? '') === 'razorpay' ? 'selected' : ''; ?>>Razorpay</option>
                    <option value="stripe" <?php echo ($settings_data['payment_gateway'] ?? '') === 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                    <option value="paypal" <?php echo ($settings_data['payment_gateway'] ?? '') === 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                </select>
            </div>

            <!-- Razorpay -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Razorpay Configuration</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Razorpay Key ID</label>
                        <input type="text" class="form-control" name="razorpay_key_id" 
                               value="<?php echo htmlspecialchars($settings_data['razorpay_key_id'] ?? ''); ?>"
                               placeholder="rzp_test_xxxxxxxxxxxxx">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Razorpay Key Secret</label>
                        <input type="password" class="form-control" name="razorpay_key_secret" 
                               value="<?php echo htmlspecialchars($settings_data['razorpay_key_secret'] ?? ''); ?>"
                               placeholder="Enter secret key">
                    </div>
                </div>
            </div>

            <!-- Stripe -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">Stripe Configuration</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stripe Public Key</label>
                        <input type="text" class="form-control" name="stripe_public_key" 
                               value="<?php echo htmlspecialchars($settings_data['stripe_public_key'] ?? ''); ?>"
                               placeholder="pk_test_xxxxxxxxxxxxx">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stripe Secret Key</label>
                        <input type="password" class="form-control" name="stripe_secret_key" 
                               value="<?php echo htmlspecialchars($settings_data['stripe_secret_key'] ?? ''); ?>"
                               placeholder="sk_test_xxxxxxxxxxxxx">
                    </div>
                </div>
            </div>

            <!-- PayPal -->
            <div class="mb-4">
                <h6 class="fw-bold mb-3">PayPal Configuration</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">PayPal Client ID</label>
                        <input type="text" class="form-control" name="paypal_client_id" 
                               value="<?php echo htmlspecialchars($settings_data['paypal_client_id'] ?? ''); ?>"
                               placeholder="Enter client ID">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">PayPal Secret</label>
                        <input type="password" class="form-control" name="paypal_secret" 
                               value="<?php echo htmlspecialchars($settings_data['paypal_secret'] ?? ''); ?>"
                               placeholder="Enter secret">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
