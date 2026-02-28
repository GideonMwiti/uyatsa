<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireExecutive();

$conn = getDBConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $full_name = sanitize($_POST['full_name']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);
    $status = sanitize($_POST['status']);
    
    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if username or email exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            // Admin created users are auto-approved
            $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password, role, status, is_approved) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("ssssss", $full_name, $username, $email, $hashed_password, $role, $status);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'User created successfully!';
                header('Location: members.php');
                exit();
            } else {
                $error = 'Failed to create user.';
            }
        }
    }
}

include 'header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Add New User</h4>
                    <a href="members.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Members
                    </a>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Initial Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role *</label>
                                <select name="role" class="form-control" required>
                                    <option value="member">Member</option>
                                    <option value="Patron">Patron</option>
                                    <option value="Chairperson">Chairperson</option>
                                    <option value="Vice_Chairperson">Vice Chairperson</option>
                                    <option value="Secretary_General">Secretary General</option>
                                    <option value="Treasurer">Treasurer</option>
                                    <option value="Organizing_Secretary">Organizing Secretary</option>
                                    <option value="Publicity_Officer">Publicity Officer</option>
                                    <option value="NextGen_Docket">NextGen Docket</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="alumni">Alumni</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="add_user" class="btn btn-primary px-4 py-2">
                                <i class="fas fa-user-plus me-2"></i> Create User Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
