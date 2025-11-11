<?php
require_once __DIR__ . '/config/config.php';

$page_title = 'About Us';

include __DIR__ . '/includes/header.php';
?>

<style>
    .about-hero {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 4rem 0;
        text-align: center;
        margin-bottom: 3rem;
    }

    .about-section {
        padding: 3rem 0;
    }

    .feature-card {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        height: 100%;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        margin: 0 auto 1.5rem;
    }

    .team-member {
        text-align: center;
        margin-bottom: 2rem;
    }

    .team-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
        margin: 0 auto 1rem;
        font-weight: 700;
    }

    .stats-section {
        background: var(--surface-color);
        padding: 3rem 0;
        border-radius: 12px;
        margin: 3rem 0;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 2rem 0;
        }

        .about-section {
            padding: 2rem 0;
        }
    }
</style>

<div class="about-hero">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">About Weikfield</h1>
        <p class="lead mb-0">Your trusted source for premium mushroom products</p>
    </div>
</div>

<div class="container">
    <!-- Our Story -->
    <section class="about-section">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1565868397984-c5e5c6d6e8f8?w=600&h=400&fit=crop" 
                     alt="Mushrooms" class="img-fluid rounded shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Our Story</h2>
                <p class="text-secondary mb-3">
                    Weikfield has been at the forefront of providing premium quality mushroom products for over two decades. 
                    What started as a small family business has grown into a trusted name in the industry.
                </p>
                <p class="text-secondary mb-3">
                    We specialize in offering a wide variety of mushroom products including fresh mushrooms, dried varieties, 
                    extracts, and DIY growing kits. Our commitment to quality and customer satisfaction has made us a 
                    preferred choice for mushroom enthusiasts worldwide.
                </p>
                <p class="text-secondary mb-0">
                    Today, we continue to innovate and expand our product range while maintaining the highest standards 
                    of quality and sustainability.
                </p>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <div class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-6 col-md-3 stat-item mb-4 mb-md-0">
                    <div class="stat-number">20+</div>
                    <div class="text-secondary">Years Experience</div>
                </div>
                <div class="col-6 col-md-3 stat-item mb-4 mb-md-0">
                    <div class="stat-number">500+</div>
                    <div class="text-secondary">Products</div>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <div class="stat-number">10K+</div>
                    <div class="text-secondary">Happy Customers</div>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <div class="stat-number">50K+</div>
                    <div class="text-secondary">Orders Delivered</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us -->
    <section class="about-section">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-3">Why Choose Us</h2>
            <p class="text-secondary">What makes Weikfield the best choice for mushroom products</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Premium Quality</h5>
                    <p class="text-secondary mb-0">
                        We source only the finest mushrooms and maintain strict quality control standards.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h5 class="fw-bold mb-3">100% Organic</h5>
                    <p class="text-secondary mb-0">
                        All our products are certified organic and grown without harmful chemicals.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Fast Delivery</h5>
                    <p class="text-secondary mb-0">
                        Quick and secure digital delivery of all our products worldwide.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5 class="fw-bold mb-3">24/7 Support</h5>
                    <p class="text-secondary mb-0">
                        Our dedicated support team is always ready to help you.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="about-section">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="feature-card text-start">
                    <h3 class="fw-bold mb-3">
                        <i class="fas fa-bullseye me-2" style="color: var(--primary-color);"></i>
                        Our Mission
                    </h3>
                    <p class="text-secondary mb-0">
                        To provide the highest quality mushroom products while promoting sustainable farming practices 
                        and educating people about the nutritional and medicinal benefits of mushrooms. We strive to 
                        make premium mushroom products accessible to everyone.
                    </p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="feature-card text-start">
                    <h3 class="fw-bold mb-3">
                        <i class="fas fa-eye me-2" style="color: var(--primary-color);"></i>
                        Our Vision
                    </h3>
                    <p class="text-secondary mb-0">
                        To become the world's leading provider of mushroom products, recognized for our commitment to 
                        quality, innovation, and sustainability. We envision a future where mushrooms are a staple in 
                        every household, contributing to better health and environmental sustainability.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-section">
        <div class="text-center" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
                                         color: white; padding: 4rem 2rem; border-radius: 12px;">
            <h2 class="fw-bold mb-3">Ready to Get Started?</h2>
            <p class="lead mb-4">Explore our wide range of premium mushroom products today!</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-light btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Browse Products
            </a>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
