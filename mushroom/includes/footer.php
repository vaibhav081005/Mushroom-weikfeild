    <!-- Footer -->
    <footer class="mt-5" style="background-color: var(--surface-color); border-top: 1px solid var(--border-color);">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-leaf me-2" style="color: var(--primary-color);"></i>
                        <?php echo SITE_NAME; ?>
                    </h5>
                    <p class="text-secondary">
                        Premium quality mushroom products delivered digitally. Explore our wide range of fresh, dried, and extract products.
                    </p>
                    <div class="social-links mt-3">
                        <a href="#" class="me-3" style="color: var(--text-color); font-size: 1.5rem;">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="me-3" style="color: var(--text-color); font-size: 1.5rem;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="me-3" style="color: var(--text-color); font-size: 1.5rem;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" style="color: var(--text-color); font-size: 1.5rem;">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>" style="color: var(--text-secondary); text-decoration: none;">
                                Home
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/products.php" style="color: var(--text-secondary); text-decoration: none;">
                                Products
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/about.php" style="color: var(--text-secondary); text-decoration: none;">
                                About Us
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/contact.php" style="color: var(--text-secondary); text-decoration: none;">
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/faq.php" style="color: var(--text-secondary); text-decoration: none;">
                                FAQ
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo SITE_URL; ?>/contact.php" style="color: var(--text-secondary); text-decoration: none;">
                                Support Ticket
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" style="color: var(--text-secondary); text-decoration: none;">
                                Privacy Policy
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" style="color: var(--text-secondary); text-decoration: none;">
                                Terms & Conditions
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2" style="color: var(--primary-color);"></i>
                            <span style="color: var(--text-secondary);"><?php echo getSetting('site_email', 'info@weikfield.com'); ?></span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2" style="color: var(--primary-color);"></i>
                            <span style="color: var(--text-secondary);"><?php echo getSetting('site_phone', '+91 1234567890'); ?></span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--primary-color);"></i>
                            <span style="color: var(--text-secondary);">Mumbai, India</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr style="border-color: var(--border-color);">
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0" style="color: var(--text-secondary);">
                        <?php echo getSetting('footer_text', '© 2024 Weikfield. All rights reserved.'); ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- MDBootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '#!') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });

        // Add to cart animation
        function addToCartAnimation(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
            button.classList.add('btn-success');
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.disabled = false;
            }, 2000);
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                const container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible fade show`;
            toast.style.cssText = 'min-width: 250px; box-shadow: var(--shadow);';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
