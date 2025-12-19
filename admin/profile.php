<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name'] ?? $user['full_name']);
    $phone = sanitize($_POST['phone'] ?? $user['phone']);

    $profile_image = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $uploadResult = uploadFile($_FILES['profile_image'], '../assets/uploads/profile/', ['jpg', 'jpeg', 'png', 'gif']);
        if (!empty($uploadResult['error'])) {
            $_SESSION['error'] = $uploadResult['error'];
        } else {
            // delete old
            if (!empty($profile_image) && file_exists('../assets/uploads/profile/' . $profile_image)) {
                @unlink('../assets/uploads/profile/' . $profile_image);
            }
            $profile_image = $uploadResult['filename'];
        }
    }

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, profile_image = ? WHERE id = ?");
    $stmt->bind_param('sssi', $full_name, $phone, $profile_image, $userId);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Profile updated successfully.';
        $_SESSION['full_name'] = $full_name;
        if (!empty($profile_image)) {
            $_SESSION['profile_image'] = $profile_image;
        }
        header('Location: profile.php'); exit();
    } else {
        $_SESSION['error'] = 'Failed to update profile.';
    }
}
?>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h3>Admin Profile</h3>
    <?php if (!empty($_SESSION['success'])): ?><div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?><div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-center p-3">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="../assets/uploads/profile/<?php echo htmlspecialchars($user['profile_image']); ?>" class="rounded-circle mb-3" width="140" height="140">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary mb-3" style="width:140px;height:140px;display:inline-block;"></div>
                <?php endif; ?>
                <h5><?php echo htmlspecialchars($user['full_name']); ?></h5>
                <p class="small text-muted"><?php echo htmlspecialchars($user['role']); ?></p>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="profile_image" class="form-control" accept="image/*">
                        <small class="text-muted">Max size: 20MB. Allowed: JPG, PNG, GIF</small>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary" name="update_profile">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
