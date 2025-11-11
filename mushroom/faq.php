<?php
require_once __DIR__ . '/config/config.php';

$page_title = 'FAQ';

// Get FAQs
$pdo = getPDOConnection();
$stmt = $pdo->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY display_order ASC");
$faqs = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .faq-container {
        padding: 3rem 0;
        min-height: 60vh;
    }

    .faq-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .faq-item {
        background: var(--surface-color);
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .faq-item:hover {
        box-shadow: var(--shadow-hover);
    }

    .faq-question {
        padding: 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        color: var(--text-color);
        transition: all 0.3s ease;
    }

    .faq-question:hover {
        background: var(--bg-color);
    }

    .faq-question i {
        color: var(--primary-color);
        transition: transform 0.3s ease;
    }

    .faq-question.active i {
        transform: rotate(180deg);
    }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        padding: 0 1.5rem;
        color: var(--text-secondary);
    }

    .faq-answer.show {
        max-height: 500px;
        padding: 0 1.5rem 1.5rem;
    }

    .contact-cta {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 12px;
        padding: 3rem;
        text-align: center;
        margin-top: 3rem;
    }

    @media (max-width: 768px) {
        .faq-container {
            padding: 2rem 0;
        }

        .contact-cta {
            padding: 2rem 1.5rem;
        }
    }
</style>

<div class="container faq-container">
    <div class="faq-header">
        <h1 class="fw-bold mb-3">
            <i class="fas fa-question-circle me-2"></i>Frequently Asked Questions
        </h1>
        <p class="text-secondary">Find answers to common questions about our products and services</p>
    </div>

    <?php if (empty($faqs)): ?>
        <div class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-secondary mb-3"></i>
            <p class="text-secondary">No FAQs available at the moment.</p>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(<?php echo $index; ?>)">
                            <span><?php echo htmlspecialchars($faq['question']); ?></span>
                            <i class="fas fa-chevron-down" id="icon-<?php echo $index; ?>"></i>
                        </div>
                        <div class="faq-answer" id="answer-<?php echo $index; ?>">
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="contact-cta">
                <h3 class="fw-bold mb-3">Still have questions?</h3>
                <p class="mb-4">Can't find the answer you're looking for? Please contact our support team.</p>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-light btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFaq(index) {
    const answer = document.getElementById('answer-' + index);
    const icon = document.getElementById('icon-' + index);
    const question = icon.closest('.faq-question');
    
    // Close all other FAQs
    document.querySelectorAll('.faq-answer').forEach((item, i) => {
        if (i !== index) {
            item.classList.remove('show');
            document.getElementById('icon-' + i).closest('.faq-question').classList.remove('active');
        }
    });
    
    // Toggle current FAQ
    answer.classList.toggle('show');
    question.classList.toggle('active');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
