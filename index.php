<?php
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UYTSA - Empowering Youth, Building Future</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/modern.css" rel="stylesheet">
    <style>
        /* Local specific styles for landing page */
        .hero-section {
            background: linear-gradient(135deg, var(--corporate-navy) 0%, var(--corporate-blue) 100%);
            color: white;
            padding: 180px 0 120px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/img/pattern.svg') opacity(0.1); /* Fallback or pattern */
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .feature-icon-box {
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            background: white;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            height: 100%;
            border-bottom: 3px solid transparent;
        }

        .feature-icon-box:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-bottom: 3px solid var(--corporate-accent);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background-color: rgba(197, 160, 89, 0.1);
            color: var(--corporate-accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 40px 20px;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--corporate-accent);
            margin-bottom: 10px;
            font-family: var(--font-heading);
        }

        .stat-label {
            color: white;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .mission-vision-bg {
            background-color: var(--corporate-light);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-corporate fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-layer-group text-gold me-2"></i>UYTSA<span class="text-gold">.</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="fas fa-bars text-navy"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#opportunities">Opportunities</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isExecutive()): ?>
                            <li class="nav-item ms-lg-3"><a class="btn btn-corporate" href="admin/dashboard.php">Admin Panel</a></li>
                        <?php else: ?>
                            <li class="nav-item ms-lg-3"><a class="btn btn-corporate" href="user/dashboard.php">My Dashboard</a></li>
                        <?php endif; ?>
                        <li class="nav-item ms-2"><a class="btn btn-outline-danger btn-sm" href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3"><a class="btn btn-gold" href="login.php">Login</a></li>
                        <li class="nav-item ms-2"><a class="btn btn-corporate" href="register.php">Join Member</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-7 animate-up">
                    <h5 class="text-gold text-uppercase letter-spacing-2 mb-3">Ulumbi Youth & Tertiary Students Association</h5>
                    <h1 class="display-3 fw-bold text-white mb-4">Empowering Youth. <br>Building <span class="text-gold">Tomorrow.</span></h1>
                    <p class="lead mb-5 text-light opacity-75" style="max-width: 600px;">
                        A premier platform connecting ambitious minds for academic excellence, professional growth, and community development. Join the network that defines the future.
                    </p>
                    <div class="d-flex gap-3">
                        <?php if (!isLoggedIn()): ?>
                            <a href="register.php" class="btn btn-gold btn-lg">Become a Member</a>
                            <a href="#about" class="btn btn-outline-light btn-lg">Learn More</a>
                        <?php else: ?>
                            <a href="user/dashboard.php" class="btn btn-gold btn-lg">Go to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block animate-up delay-200">
                    <!-- Abstract geometric shape or illustration placeholder -->
                     <img src="https://ui-avatars.com/api/?name=UYTSA&background=random&size=512" alt="UYTSA Community" class="img-fluid rounded-3 shadow-premium glass-card p-2 border-0" style="opacity: 0.9;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features / Why Join -->
    <section id="about" class="section-padding">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h6 class="text-gold text-uppercase fw-bold">Why Choose UYTSA</h6>
                    <h2 class="display-6 fw-bold mb-3 text-navy">A Complete Ecosystem for Growth</h2>
                    <p class="text-muted">We provide a structured environment where students and youth can thrive through collaboration, mentorship, and access to exclusive resources.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-icon-box">
                        <div class="feature-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4>Academic Excellence</h4>
                        <p class="text-muted">Access curated study materials, past papers, and research resources shared by top performers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon-box">
                        <div class="feature-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h4>Career Opportunities</h4>
                        <p class="text-muted">Direct access to internships, job listings, and scholarship opportunities vetted for our members.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-icon-box">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Community Network</h4>
                        <p class="text-muted">Connect with alumni and peers. Participate in events, hackathons, and community drives.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Wrapper -->
    <section class="section-padding bg-navy text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Active Members</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Events Hosted</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">200+</div>
                        <div class="stat-label">Resources Shared</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Commitment</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section id="opportunities" class="section-padding mission-vision-bg">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <!-- Glassmorphism card for content -->
                    <div class="glass-card bg-white shadow-lg">
                        <h3 class="mb-4 text-navy">Our Strategic Vision</h3>
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-bullseye fa-2x text-gold"></i>
                            </div>
                            <div class="ms-4">
                                <h5>Our Mission</h5>
                                <p class="text-muted">To unify, empower, and advocate for the youth and students of Ulumbi, fostering a culture of excellence, integrity, and social responsibility.</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-eye fa-2x text-gold"></i>
                            </div>
                            <div class="ms-4">
                                <h5>Our Vision</h5>
                                <p class="text-muted">To be the leading youth organization that transforms lives through education, innovation, and sustainable community development.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <h6 class="text-gold text-uppercase fw-bold">Recent Updates</h6>
                    <h2 class="mb-4 text-navy">Latest Opportunities</h2>
                    <p class="text-muted mb-4">Stay ahead with the latest announcements from our partners and administration.</p>
                    
                    <div class="list-group list-group-flush bg-transparent">
                        <div class="list-group-item bg-transparent border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold text-navy">Annual Leadership Summit</h6>
                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> Coming this August</small>
                                </div>
                                <span class="badge bg-navy rounded-pill">Event</span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold text-navy">Global Scholarship Program</h6>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i> Deadline: Oct 30</small>
                                </div>
                                <span class="badge bg-gold text-dark rounded-pill">Scholarship</span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold text-navy">Tech Skills Workshop</h6>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> Community Hall</small>
                                </div>
                                <span class="badge bg-success rounded-pill">Workshop</span>
                            </div>
                        </div>
                    </div>
                    
                    <a href="register.php" class="btn btn-link text-gold fw-bold text-decoration-none mt-3">View All Opportunities <i class="fas fa-arrow-right list-group-item-action"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-corporate" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <a class="navbar-brand text-white mb-3 d-block" href="#">
                        <i class="fas fa-layer-group text-gold me-2"></i><span class="text-gold">UYTSA</span><span class="text-gold">.</span>
                    </a>
                    <p class="text-white-50">Empowering the next generation of leaders through education, connection, and community service.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4 mb-lg-0">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home">Home</a></li>
                        <li class="mb-2"><a href="#about">About Us</a></li>
                        <li class="mb-2"><a href="#opportunities">Opportunities</a></li>
                        <li class="mb-2"><a href="login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-gold"></i> Ulumbi, Kenya</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2 text-gold"></i> info@uytsa.org</li>
                        <li class="mb-2"><i class="fas fa-phone me-2 text-gold"></i> +254 712 345 678</li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <h5>Subscribe</h5>
                    <form class="subscribe-form d-flex" action="#" method="post" autocomplete="off" style="max-width: 350px;">
                        <input type="email" name="subscribe_email" class="form-control form-control-sm rounded-start-pill border-end-0" placeholder="Your email address" required style="border-top-right-radius:0;border-bottom-right-radius:0;">
                        <button type="submit" class="btn btn-gold btn-sm rounded-end-pill px-4" style="border-top-left-radius:0;border-bottom-left-radius:0;">Subscribe</button>
                    </form>
                    <small class="text-white-50 d-block mt-2" style="max-width:350px;">Get updates on events, news, and opportunities.</small>
                </div>
                    <style>
                        .subscribe-form input[type="email"] {
                            border-radius: 50px 0 0 50px;
                            border: 1px solid #e2e8f0;
                            padding: 10px 16px;
                            font-size: 0.95rem;
                            border-right: none;
                        }
                        .subscribe-form input[type="email"]:focus {
                            border-color: var(--corporate-accent);
                            box-shadow: 0 0 0 0.15rem rgba(197, 160, 89, 0.15);
                        }
                        .subscribe-form button[type="submit"] {
                            border-radius: 0 50px 50px 0;
                            font-weight: 600;
                            letter-spacing: 0.5px;
                            border: 1px solid var(--corporate-accent);
                            border-left: none;
                        }
                        @media (max-width: 992px) {
                            .subscribe-form {
                                flex-direction: column !important;
                                max-width: 100% !important;
                            }
                            .subscribe-form input[type="email"],
                            .subscribe-form button[type="submit"] {
                                border-radius: 50px !important;
                                border: 1px solid #e2e8f0 !important;
                                border-left: 1px solid #e2e8f0 !important;
                                border-right: 1px solid #e2e8f0 !important;
                                margin-bottom: 8px;
                            }
                        }
                    </style>
                <style>
                    .subscribe-form input[type="email"] {
                        border-radius: 50px;
                        border: 1px solid #e2e8f0;
                        padding: 10px 16px;
                        font-size: 0.95rem;
                    }
                    .subscribe-form input[type="email"]:focus {
                        border-color: var(--corporate-accent);
                        box-shadow: 0 0 0 0.15rem rgba(197, 160, 89, 0.15);
                    }
                    .subscribe-form button[type="submit"] {
                        border-radius: 50px;
                        font-weight: 600;
                        letter-spacing: 0.5px;
                    }
                </style>
            </div>
            <div class="footer-bottom text-center">
                <p>&copy; <?php echo date('Y'); ?> Ulumbi Youth & Tertiary Students Association. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('shadow-sm', 'bg-white');
                document.querySelector('.navbar').style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                // Keep the glass effect but maybe reduced shadow if at top
            }
        });
    </script>
</body>
</html>