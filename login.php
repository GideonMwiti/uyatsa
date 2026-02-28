<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Support both modern parsed BCRYPT hashes and legacy plain-text development logins
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['profile_image'] = $user['profile_image'];
                $_SESSION['institution'] = $user['institution'];
                
                // Check if account is approved
                if ($user['is_approved'] == 0) {
                    session_destroy();
                    session_start();
                    $error = 'Your account is pending administrator approval.';
                } else {
                    // Update last login
                    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    
                    // Redirect based on role
                    $executiveRoles = ['Patron', 'Chairperson', 'Vice_Chairperson', 'Secretary_General',
                                     'Treasurer', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket'];
                    
                    if (in_array($user['role'], $executiveRoles)) {
                        header('Location: admin/dashboard.php');
                    } else {
                        header('Location: user/dashboard.php');
                    }
                    exit();
                }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UYTSA Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/modern.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            overflow: hidden;
        }
        
        .split-screen {
            display: flex;
            height: 100vh;
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
            position: relative;
        }
        
        .left-pane::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('assets/img/pattern.svg'); /* Optional pattern */
            opacity: 0.05;
        }
        
        .right-pane {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 400px;
        }
        
        .brand-logo {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--corporate-accent);
        }
        
        /* Mobile adjustment */
        @media (max-width: 768px) {
            .split-screen {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }
            
            .left-pane {
                flex: 0 0 200px;
                padding: 20px;
            }
            
            .right-pane {
                flex: 1;
                padding: 30px 20px;
            }
            
            body, html {
                overflow: auto;
            }
        }
        
        .form-floating > .form-control {
            border-color: #e2e8f0;
        }
        
        .form-floating > .form-control:focus {
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
            <h2 class="fw-bold mb-3 text-white">Welcome Back!</h2>
            <p class="lead opacity-75">Connect, collaborate, and grow with the UYTSA community.</p>
            <a href="index.php" class="btn btn-outline-light btn-sm mt-4 rounded-pill px-4">
                <i class="fas fa-arrow-left me-2"></i> Back to Home
            </a>
        </div>
        
        <div class="right-pane">
            <div class="login-wrapper animate-up">
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-navy">Member Login</h3>
                    <p class="text-muted">Enter your credentials to access your dashboard.</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username or Email" required>
                        <label for="username">Username or Email</label>
                    </div>
                    
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                    
                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-corporate py-3">Sign In</button>
                    </div>
                    
                    <div class="text-center">
                        <span class="text-muted">Don't have an account?</span> 
                        <a href="register.php" class="text-navy fw-bold text-decoration-none">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>