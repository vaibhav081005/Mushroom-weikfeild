<?php
require_once __DIR__ . '/config/config.php';

$page_title = 'Contact Us';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $pdo = getPDOConnection();
        $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
        
        $stmt = $pdo->prepare("
            INSERT INTO support_tickets (user_id, name, email, subject, message, status)
            VALUES (?, ?, ?, ?, ?, 'open')
        ");
        
        if ($stmt->execute([$user_id, $name, $email, $subject, $message])) {
            $success = 'Your message has been sent successfully! We will get back to you soon.';
            
            // Send notification email to admin
            $admin_email = getSetting('site_email', 'info@weikfield.com');
            $email_subject = 'New Support Ticket: ' . $subject;
            $email_message = "
                <h2>New Support Ticket</h2>
                <p><strong>From:</strong> $name ($email)</p>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong></p>
                <p>$message</p>
            ";
            sendEmail($admin_email, $email_subject, $email_message);
            
            // Clear form
            $name = $email = $subject = $message = '';
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .contact-container {
        padding: 3rem 0;
    }

    .contact-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .contact-form-card {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: var(--shadow);
    }

    .contact-info-card {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 12px;
        padding: 2.5rem;
        height: 100%;
    }

    .contact-info-item {
        display: flex;
        align-items: start;
        margin-bottom: 2rem;
    }

    .contact-info-item:last-child {
        margin-bottom: 0;
    }

    .contact-icon {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .form-control, .form-select {
        padding: 0.75rem 1rem;
        border-radius: 8px;
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .social-link {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: white;
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .contact-container {
            padding: 2rem 0;
        }

        .contact-form-card,
        .contact-info-card {
            padding: 1.5rem;
        }
    }
</style>

<div class="container contact-container">
    <div class="contact-header">
        <h1 class="fw-bold mb-3">
            <i class="fas fa-envelope me-2"></i>Contact Us
        </h1>
        <p class="text-secondary">Have a question? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>

    <div class="row g-4">
        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="contact-form-card">
                <h4 class="fw-bold mb-4">Send us a Message</h4>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Your Name *</label>
                            <input type="text" class="form-control" name="name" required
                                   value="<?php echo htmlspecialchars($name ?? ''); ?>">
                            <div class="invalid-feedback">Please enter your name.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" required
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Subject *</label>
                            <input type="text" class="form-control" name="subject" required
                                   value="<?php echo htmlspecialchars($subject ?? ''); ?>">
                            <div class="invalid-feedback">Please enter a subject.</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Message *</label>
                            <textarea class="form-control" name="message" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please enter your message.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="col-lg-5">
            <div class="contact-info-card">
                <h4 class="fw-bold mb-4">Get in Touch</h4>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Address</h6>
                        <p class="mb-0">Mumbai, Maharashtra, India</p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Phone</h6>
                        <p class="mb-0"><?php echo getSetting('site_phone', '+91 1234567890'); ?></p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Email</h6>
                        <p class="mb-0"><?php echo getSetting('site_email', 'info@weikfield.com'); ?></p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Business Hours</h6>
                        <p class="mb-0">Monday - Friday: 9:00 AM - 6:00 PM</p>
                        <p class="mb-0">Saturday: 10:00 AM - 4:00 PM</p>
                        <p class="mb-0">Sunday: Closed</p>
                    </div>
                </div>

                <hr style="border-color: rgba(255,255,255,0.2); margin: 2rem 0;">

                <h6 class="fw-bold mb-3">Follow Us</h6>
                <div class="social-links">
                    <a href="#" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
