<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UYTSA Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/shared.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="../assets/css/modern.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="css/admin.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>
<!-- Admin Header -->
<header class="admin-header-bar" style="width:100%;background:var(--corporate-navy,#1a237e);color:#fff;padding:12px 0;box-shadow:0 2px 8px rgba(0,0,0,0.05);margin-bottom:0;position:sticky;top:0;z-index:1000;">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-white d-md-none me-2 p-0" id="sidebarToggle" type="button" style="text-decoration:none;">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <div class="d-flex align-items-center justify-content-center fw-bold" style="height:40px;width:40px;border-radius:50%;background:#ffffff;color:#1a237e;margin-right:12px;font-size:1.2rem;flex-shrink:0;box-shadow:0 2px 5px rgba(0,0,0,0.2);">
                UY
            </div>
            <?php $headerRole = isset($_SESSION['role']) ? str_replace('_', ' ', $_SESSION['role']) : 'Admin'; ?>
            <span class="fw-bold fs-5 d-none d-sm-inline">UYTSA <?= htmlspecialchars($headerRole) ?> Portal</span>
        </div>
        <div class="d-flex align-items-center">
            <a href="../index.php" class="btn btn-light btn-sm me-3" style="background:#fff;color:#1a237e;border-radius:20px;font-weight:500;box-shadow:0 1px 4px rgba(0,0,0,0.07);"><i class="fas fa-home me-1"></i> Back to Website</a>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="adminProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="../assets/uploads/profile/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #fff;">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-2x text-white"></i>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminProfileDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>