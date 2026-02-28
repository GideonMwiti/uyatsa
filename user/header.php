<?php
if (!isset($_SESSION)) {
    session_start();
}
$notifCount = 0;
$notes = null;
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../includes/functions.php';
    $notifCount = countUnreadNotifications($_SESSION['user_id']);
    $notes = getNotificationsForUser($_SESSION['user_id'], 5);
}
?>
<!-- User Header -->
<header class="admin-header-bar" style="width:100%;background:var(--corporate-navy,#1a237e);color:#fff;padding:12px 0;box-shadow:0 2px 8px rgba(0,0,0,0.05);margin-bottom:0;position:sticky;top:0;z-index:1000;">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-white d-md-none me-2 p-0" id="sidebarToggle" type="button" style="text-decoration:none;">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <div class="d-flex align-items-center justify-content-center fw-bold d-none d-sm-flex" style="height:40px;width:40px;border-radius:50%;background:#ffffff;color:#1a237e;margin-right:12px;font-size:1.2rem;flex-shrink:0;box-shadow:0 2px 5px rgba(0,0,0,0.2);">
                UY
            </div>
            <span class="fw-bold fs-5">UYTSA Member Portal</span>
        </div>
        <div class="d-flex align-items-center">
            <a href="../index.php" class="btn btn-light btn-sm me-3 d-none d-md-inline-block" style="background:#fff;color:#1a237e;border-radius:20px;font-weight:500;box-shadow:0 1px 4px rgba(0,0,0,0.07);"><i class="fas fa-home me-1"></i> Back to Website</a>
            
            <!-- Notifications Dropdown -->
            <div class="dropdown me-3">
                <a class="nav-link position-relative text-white p-0 dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="display:inline-block; margin-top:5px;">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill <?php echo $notifCount ? '' : 'd-none'; ?>" style="font-size:0.6rem; padding: 2px 5px;"><?php echo $notifCount; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2 shadow" aria-labelledby="notifDropdown" style="min-width:300px; border-radius:12px; border:none; z-index:1050;">
                    <li class="dropdown-header fw-bold text-navy">Notifications</li>
                    <?php if (!empty($notes) && $notes->num_rows > 0): ?>
                        <?php while($n = $notes->fetch_assoc()): ?>
                            <li>
                                <a class="dropdown-item notification-link border-bottom py-2 <?php echo $n['is_read'] ? '' : 'fw-bold bg-light'; ?>" href="<?php echo htmlspecialchars($n['link'] ?? '#'); ?>" data-notification-id="<?php echo $n['id']; ?>" style="white-space: normal;">
                                    <div class="d-flex w-100 justify-content-between">
                                        <div class="text-navy fw-bold" style="font-size: 0.9rem;"><?php echo htmlspecialchars($n['title']); ?></div>
                                        <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('M d', strtotime($n['created_at'])); ?></small>
                                    </div>
                                    <div class="small text-muted mt-1" style="font-size: 0.8rem; line-height:1.2;"><?php echo htmlspecialchars($n['message']); ?></div>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li><div class="dropdown-item small text-muted text-center py-3">No notifications</div></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item text-center small text-primary fw-bold mt-2" href="notifications.php">View All Notifications</a></li>
                </ul>
            </div>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if (!empty($_SESSION['profile_image'])): ?>
                        <img src="../<?php echo defined('PROFILE_IMAGE_PATH') ? PROFILE_IMAGE_PATH : 'assets/uploads/profile/'; ?><?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #fff;">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-2x text-white"></i>
                    <?php endif; ?>
                    <span class="ms-2 d-none d-md-inline"><?php echo htmlspecialchars(explode(' ', trim($_SESSION['full_name'] ?? ''))[0]); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userProfileDropdown" style="border-radius:12px; border:none; margin-top:10px; z-index:1050;">
                    <li><a class="dropdown-item py-2" href="profile.php"><i class="fas fa-user text-muted me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item py-2" href="my-contributions.php"><i class="fas fa-money-bill-wave text-muted me-2"></i> My Contributions</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>