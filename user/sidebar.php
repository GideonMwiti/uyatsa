<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$full_name = $_SESSION['full_name'] ?? 'Member';
$institution = $_SESSION['institution'] ?? '';
$role = $_SESSION['role'] ?? 'Member';
if (!empty($_SESSION['profile_image'])) {
    $profile_img = '../' . (defined('PROFILE_IMAGE_PATH') ? PROFILE_IMAGE_PATH : 'assets/uploads/profile/') . $_SESSION['profile_image'];
}
$unread_notifications = 0;
if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../includes/functions.php';
    $unread_notifications = countUnreadNotifications($_SESSION['user_id']);
}
?>

<div class="sidebar-overlay d-md-none" id="sidebarOverlay"></div>
<div class="user-sidebar-wrapper" id="userSidebar">
    <div class="d-flex justify-content-end d-md-none mb-2">
        <button class="btn btn-link text-white p-0" id="sidebarClose" type="button" style="text-decoration:none;">
            <i class="fas fa-times fa-lg"></i>
        </button>
    </div>
        <div class="text-center mb-3">
        <?php if (!empty($profile_img)): ?>
            <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile" class="profile-img">
        <?php else: ?>
            <div class="profile-img bg-white d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:100px; height:100px; border-radius:50%; border: 3px solid rgba(255,255,255,0.2);">
                <i class="fas fa-user fa-3x" style="color:#1a237e;"></i>
            </div>
        <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('userSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (sidebar && overlay) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : ''; // Prevent body scroll
        }
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);
});
</script>
