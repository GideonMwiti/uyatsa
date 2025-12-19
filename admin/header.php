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
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/shared.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
</head>
<body>
<!-- Admin Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-users"></i> UYTSA Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center" href="profile.php">
                        <?php if (!empty($_SESSION['profile_image'])): ?>
                            <img src="../assets/uploads/profile/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="avatar" style="width:30px;height:30px;border-radius:50%;object-fit:cover;margin-right:8px;">
                        <?php else: ?>
                            <i class="fas fa-user-shield me-2"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?> <small class="ms-2 text-muted">(<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)</small>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">
                        <i class="fas fa-home"></i> Public Site
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>