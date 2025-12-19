<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$full_name = $_SESSION['full_name'] ?? 'Member';
$institution = $_SESSION['institution'] ?? '';
$role = $_SESSION['role'] ?? 'Member';
$profile_img = '';
if (!empty($_SESSION['profile_image'])) {
    $profile_img = '../' . (defined('PROFILE_IMAGE_PATH') ? PROFILE_IMAGE_PATH : 'assets/uploads/profile/') . $_SESSION['profile_image'];
} else {
    $profile_img = 'https://via.placeholder.com/120';
}
$unread_notifications = 0;
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../includes/functions.php';
    $unread_notifications = countUnreadNotifications($_SESSION['user_id']);
}
?>

<style>
    .user-sidebar-wrapper {
        background: linear-gradient(180deg, #08203a 0%, #0f3a63 100%);
        color: #f8fbff;
        min-height: 100vh;
        padding: 30px 18px;
        border-radius: 12px;
    }
    .user-sidebar-wrapper .profile-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255,255,255,0.12);
        display: block;
        margin: 0 auto 10px auto;
    }
    .user-sidebar-wrapper h5 {
        color: #f8fbff;
        font-weight: 700;
        margin-bottom: 6px;
        text-align: center;
        text-shadow: 0 1px 2px rgba(0,0,0,0.45);
    }
    .user-role-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 14px;
        background: rgba(255,255,255,0.12);
        color: #fff;
        font-size: 0.85rem;
        text-align: center;
    }
    .notif-badge {
        display: inline-block;
        min-width: 26px;
        padding: 4px 8px;
        border-radius: 14px;
        background: #dc3545;
        color: #fff;
        font-weight: 700;
        margin-left: 8px;
        font-size: 0.85rem;
    }
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #ffffff;
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 8px;
        font-size: 1.05rem;
        font-weight: 700;
        text-shadow: 0 1px 2px rgba(0,0,0,0.45);
        opacity: 1;
    }
    .sidebar-link .sidebar-icon { width: 28px; text-align:center; font-size:1.2rem; }
    .sidebar-link:hover { background: rgba(255,255,255,0.08); text-decoration: none; color: #ffffff; }
    .sidebar-link.active { background: rgba(255,255,255,0.14); font-weight:800; color: #ffffff; }

    .sidebar-link .sidebar-icon { color: #ffffff; opacity: 0.95; }
    @media (max-width: 767px) {
        .user-sidebar-wrapper {
            padding: 18px 12px;
            border-radius: 0;
            min-height: auto;
        }
        .user-sidebar-wrapper .profile-img { width: 80px; height: 80px; }
        .sidebar-link { font-size: 0.95rem; padding: 8px 10px; }
    }
</style>

<div class="user-sidebar-wrapper">
        <div class="text-center mb-3">
        <img src="<?php echo $profile_img; ?>" alt="Profile" class="profile-img">
        <h5><?php echo htmlspecialchars($institution ?: $full_name); ?></h5>
        <div>
            <span class="user-role-badge"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
            <?php if ($unread_notifications > 0): ?>
                <span class="notif-badge"><?php echo $unread_notifications; ?></span>
            <?php endif; ?>
        </div>
    </div>

    <nav class="nav flex-column">
        <a href="dashboard.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt sidebar-icon"></i> <span>Dashboard</span>
        </a>
        <a href="profile.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
            <i class="fas fa-user sidebar-icon"></i> <span>My Profile</span>
        </a>
        <a href="opportunities.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'opportunities.php') ? 'active' : ''; ?>">
            <i class="fas fa-briefcase sidebar-icon"></i> <span>Opportunities</span>
        </a>
        <a href="announcements.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'announcements.php') ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn sidebar-icon"></i> <span>Announcements</span>
        </a>
        <a href="gallery.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'gallery.php') ? 'active' : ''; ?>">
            <i class="fas fa-images sidebar-icon"></i> <span>Gallery</span>
        </a>
        <a href="events.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'events.php') ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt sidebar-icon"></i> <span>Events</span>
        </a>
        <a href="study-materials.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'study-materials.php') ? 'active' : ''; ?>">
            <i class="fas fa-book sidebar-icon"></i> <span>Study Materials</span>
        </a>
        <a href="calamity-response.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'calamity-response.php') ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle sidebar-icon"></i> <span>Calamity Response</span>
        </a>
        <a href="my-contributions.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'my-contributions.php') ? 'active' : ''; ?>">
            <i class="fas fa-money-bill-wave sidebar-icon"></i> <span>My Contributions</span>
        </a>
        <a href="../logout.php" class="sidebar-link">
            <i class="fas fa-sign-out-alt sidebar-icon"></i> <span>Logout</span>
        </a>
    </nav>
</div>

