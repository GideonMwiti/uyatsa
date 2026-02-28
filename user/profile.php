<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $institution = sanitize($_POST['institution']);
    $course = sanitize($_POST['course']);
    $year_of_study = sanitize($_POST['year_of_study']);
    $graduation_year = sanitize($_POST['graduation_year']);
    $bio = sanitize($_POST['bio']);
    
    // Handle profile image upload
    $profile_image = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $uploadResult = uploadFile($_FILES['profile_image'], '../assets/uploads/profile/', ['jpg', 'jpeg', 'png', 'gif']);
        if (isset($uploadResult['success'])) {
            // Delete old image if exists
            if (!empty($profile_image) && file_exists('../assets/uploads/profile/' . $profile_image)) {
                unlink('../assets/uploads/profile/' . $profile_image);
            }
            $profile_image = $uploadResult['filename'];
        }
    }
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, institution = ?, course = ?, year_of_study = ?, graduation_year = ?, bio = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param("sssssisss", $full_name, $phone, $institution, $course, $year_of_study, $graduation_year, $bio, $profile_image, $userId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Profile updated successfully!';
        $_SESSION['full_name'] = $full_name;
        $_SESSION['profile_image'] = $profile_image;
        $_SESSION['institution'] = $institution;
        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update profile.';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
    } elseif (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Password changed successfully!';
            header('Location: profile.php');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to change password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            padding: 40px 0;
            border-radius: 10px 10px 0 0;
        }
        
        .profile-image {
            width: 150px;
            height: 150px;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .profile-stat {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
    </style>
    <link href="../assets/css/modern.css" rel="stylesheet">
    <link href="css/user.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="dashboard-row">
            <?php include 'sidebar.php'; ?>
            <div class="dashboard-main-content">
                 <div class="p-4 w-100">
                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Profile Header -->
                <div class="card mb-4">
                    <div class="profile-header text-center">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="../assets/uploads/profile/<?php echo $user['profile_image']; ?>" 
                                 class="rounded-circle profile-image mb-3" 
                                 alt="Profile Image">
                        <?php else: ?>
                            <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center mb-3 profile-image">
                                <i class="fas fa-user text-primary" style="font-size: 60px;"></i>
                            </div>
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="mb-0"><?php echo htmlspecialchars($user['role']); ?> | Member Since: <?php echo date('F Y', strtotime($user['registration_date'])); ?></p>
                    </div>
                    
                    <!-- Profile Stats -->
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="profile-stat">
                                    <h5><i class="fas fa-briefcase text-primary"></i></h5>
                                    <h4><?php 
                                        $result = $conn->query("SELECT COUNT(*) as count FROM opportunities WHERE posted_by = $userId");
                                        echo $result->fetch_assoc()['count'];
                                    ?></h4>
                                    <p>Opportunities</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="profile-stat">
                                    <h5><i class="fas fa-images text-success"></i></h5>
                                    <h4><?php 
                                        $result = $conn->query("SELECT COUNT(*) as count FROM gallery WHERE uploaded_by = $userId");
                                        echo $result->fetch_assoc()['count'];
                                    ?></h4>
                                    <p>Gallery Uploads</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="profile-stat">
                                    <h5><i class="fas fa-calendar-check text-warning"></i></h5>
                                    <h4><?php 
                                        $result = $conn->query("SELECT COUNT(*) as count FROM event_registrations WHERE user_id = $userId");
                                        echo $result->fetch_assoc()['count'];
                                    ?></h4>
                                    <p>Events</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="profile-stat">
                                    <h5><i class="fas fa-money-bill-wave text-info"></i></h5>
                                    <h4><?php 
                                        $result = $conn->query("SELECT COUNT(*) as count FROM finances WHERE member_id = $userId");
                                        echo $result->fetch_assoc()['count'];
                                    ?></h4>
                                    <p>Contributions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Information Tabs -->
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                                    <i class="fas fa-user"></i> Personal Info
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="profileTabsContent">
                            <!-- Personal Info Tab -->
                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Full Name *</label>
                                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                            <small class="text-muted">Username cannot be changed</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Institution</label>
                                            <input type="text" name="institution" class="form-control" value="<?php echo htmlspecialchars($user['institution'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Course/Program</label>
                                            <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($user['course'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Year of Study</label>
                                            <select name="year_of_study" class="form-control">
                                                <option value="">Select Year</option>
                                                <option value="1st Year" <?php echo ($user['year_of_study'] ?? '') == '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                                                <option value="2nd Year" <?php echo ($user['year_of_study'] ?? '') == '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                                                <option value="3rd Year" <?php echo ($user['year_of_study'] ?? '') == '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                                                <option value="4th Year" <?php echo ($user['year_of_study'] ?? '') == '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                                                <option value="5th Year+" <?php echo ($user['year_of_study'] ?? '') == '5th Year+' ? 'selected' : ''; ?>>5th Year+</option>
                                                <option value="Postgraduate" <?php echo ($user['year_of_study'] ?? '') == 'Postgraduate' ? 'selected' : ''; ?>>Postgraduate</option>
                                                <option value="Alumni" <?php echo ($user['year_of_study'] ?? '') == 'Alumni' ? 'selected' : ''; ?>>Alumni</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Graduation Year</label>
                                            <select name="graduation_year" class="form-control">
                                                <option value="">Select Year</option>
                                                <?php for ($year = date('Y'); $year <= date('Y') + 10; $year++): ?>
                                                    <option value="<?php echo $year; ?>" <?php echo $user['graduation_year'] == $year ? 'selected' : ''; ?>>
                                                        <?php echo $year; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Profile Image</label>
                                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                                        <small class="text-muted">Max size: 5MB. Allowed: JPG, PNG, GIF</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Bio</label>
                                        <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                            
                            <!-- Change Password Tab -->
                            <div class="tab-pane fade" id="password" role="tabpanel">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password *</label>
                                        <input type="password" name="current_password" class="form-control" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">New Password *</label>
                                        <input type="password" name="new_password" class="form-control" required>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirm New Password *</label>
                                        <input type="password" name="confirm_password" class="form-control" required>
                                    </div>
                                    
                                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</div></body>
</html>