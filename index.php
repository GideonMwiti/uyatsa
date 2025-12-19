<?php
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UYTSA - Ulumbi Youth & Tertiary Students Association</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --uytsa-primary: #2c3e50;
            --uytsa-secondary: #3498db;
            --uytsa-accent: #e74c3c;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--uytsa-primary) 0%, var(--uytsa-secondary) 100%);
            color: white;
            padding: 100px 0;
        }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .btn-uytsa {
            background-color: var(--uytsa-accent);
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
        }
        
        .btn-uytsa:hover {
            background-color: #c0392b;
            color: white;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--uytsa-primary) !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users text-primary"></i> UYTSA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isExecutive()): ?>
                            <li class="nav-item"><a class="nav-link btn btn-primary btn-sm text-white ms-2" href="admin/dashboard.php">Admin Dashboard</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link btn btn-primary btn-sm text-white ms-2" href="user/dashboard.php">Member Dashboard</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link btn btn-outline-danger btn-sm ms-2" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn btn-primary btn-sm text-white ms-2" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link btn btn-outline-primary btn-sm ms-2" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Welcome to UYTSA Community Platform</h1>
                    <p class="lead mb-4">Connecting Ulumbi Youth & Tertiary Students for academic excellence, opportunity sharing, and community development.</p>
                    <?php if (!isLoggedIn()): ?>
                        <a href="register.php" class="btn btn-light btn-lg me-3">Join Now</a>
                        <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6">
                    <img src="" alt="UYTSA Community" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Platform Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card feature-card shadow">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                            <h4>Academic Hub</h4>
                            <p>Access study materials, mentorship programs, and academic resources.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-briefcase fa-3x text-success mb-3"></i>
                            <h4>Opportunities</h4>
                            <p>Find internships, scholarships, jobs, and skill-building opportunities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card shadow">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-camera fa-3x text-warning mb-3"></i>
                            <h4>Memory Gallery</h4>
                            <p>Share and view photos from events, expeditions, and volunteering.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2>About UYTSA</h2>
                    <p class="lead">The Ulumbi Youth & Tertiary Students Association (UYTSA) is a community-driven organization dedicated to empowering youth through education, opportunity sharing, and community engagement.</p>
                    <p>Our platform serves as a digital hub for all members to connect, collaborate, and grow together.</p>
                </div>
                <div class="col-lg-6">
                    <h4>Our Mission</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Unify all Ulumbi youth and students</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Promote academic excellence</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Share opportunities and resources</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Ensure financial transparency</li>
                        <li class="list-group-item"><i class="fas fa-check text-success me-2"></i>Foster community development</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p>Contact: info@uytsa.org | +254 712 345 678</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>