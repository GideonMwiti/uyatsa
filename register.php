<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);
    $institution = sanitize($_POST['institution']);
    $course = sanitize($_POST['course']);
    $year_of_study = sanitize($_POST['year_of_study']);
    
    // Validation
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();
        
        // Check if username or email exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user natively as a member that requires admin approval
            $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password, phone, institution, course, year_of_study, role, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'member', 0)");
            $stmt->bind_param("ssssssss", $full_name, $username, $email, $hashed_password, $phone, $institution, $course, $year_of_study);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! An administrator must approve your account before you can log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Us - UYTSA Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/modern.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            background-color: #fff;
        }
        
        .split-screen {
            display: flex;
            min-height: 100vh;
        }

        .left-pane {
            flex: 1;
            background: linear-gradient(135deg, var(--corporate-navy) 0%, var(--corporate-blue) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            min-height: 100vh;
            z-index: 1;
        }

        .right-pane {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            min-height: 100vh;
        }
        
        .register-wrapper {
            width: 100%;
            max-width: 600px;
        }
        
        .brand-logo {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--corporate-accent);
        }
        
        /* Mobile adjustment */
        @media (max-width: 992px) {
            .split-screen {
                flex-direction: column;
            }
            .left-pane {
                width: 100%;
                min-height: 300px;
                flex: none;
                padding: 40px 20px;
                position: relative;
            }
            .right-pane {
                padding: 40px 20px;
            }
        }
        
        .form-label {
            font-weight: 500;
            color: var(--corporate-navy);
            font-size: 0.9rem;
        }
        
        .form-control {
            padding: 12px;
            border-color: #e2e8f0;
        }
        
        .form-control:focus {
            border-color: var(--corporate-blue);
            box-shadow: 0 0 0 0.25rem rgba(17, 34, 64, 0.1);
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <div class="left-pane text-center">
            <div class="brand-logo">
                <i class="fas fa-layer-group"></i>
            </div>
            <h2 class="fw-bold mb-3 text-white">Join the Movement</h2>
            <p class="lead opacity-75 mb-4">Be part of a thriving community committed to excellence and service.</p>
            
            <ul class="list-unstyled text-start d-inline-block mx-auto opacity-75">
                <li class="mb-2"><i class="fas fa-check text-gold me-2"></i> Access Exclusive Resources</li>
                <li class="mb-2"><i class="fas fa-check text-gold me-2"></i> Mentorship Opportunities</li>
                <li class="mb-2"><i class="fas fa-check text-gold me-2"></i> Community Events</li>
            </ul>
            
            <a href="index.php" class="btn btn-outline-light btn-sm mt-5 rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Back to Home
            </a>
        </div>
        
        <div class="right-pane">
            <div class="register-wrapper animate-up">
                <div class="mb-4">
                    <h3 class="fw-bold text-navy">Create Your Account</h3>
                    <p class="text-muted">Fill in your details to get started.</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="johndoe" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="john@example.com" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+254...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="institution" class="form-label">Institution</label>
                        <input type="text" class="form-control" id="institution" name="institution" placeholder="University/College Name">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="course" class="form-label">Course/Program</label>
                            <input type="text" class="form-control" id="course" name="course" placeholder="e.g. Computer Science">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="year_of_study" class="form-label">Year of Study</label>
                            <select class="form-select" id="year_of_study" name="year_of_study">
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                                <option value="5th Year+">5th Year+</option>
                                <option value="Postgraduate">Postgraduate</option>
                                <option value="Alumni">Alumni</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-corporate py-3">Create Account</button>
                    </div>
                    
                    <div class="text-center mt-4">
                        <span class="text-muted">Already have an account?</span>
                        <a href="login.php" class="text-navy fw-bold text-decoration-none">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>