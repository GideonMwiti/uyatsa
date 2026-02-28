<!-- Admin Sidebar -->
<div class="sidebar-overlay d-md-none" id="sidebarOverlay"></div>
<div class="col-md-2 p-0 sidebar-wrapper" id="adminSidebar">
    <div class="p-3 text-white">
        <div style="position:absolute;top:0;left:100%;width:18px;height:100px;background:#fff;z-index:2;box-shadow:2px 0 8px rgba(0,0,0,0.05);border-radius:0 0 12px 12px;" class="d-none d-md-block"></div>
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <?php $sidebarRole = isset($_SESSION['role']) ? str_replace('_', ' ', $_SESSION['role']) : 'Admin'; ?>
            <h4 class="mb-0 mx-auto text-center" style="font-size: 1.1rem;">
                <i class="fas fa-users-cog"></i> <?= htmlspecialchars($sidebarRole) ?> Portal
            </h4>
            <button class="btn btn-link text-white d-md-none p-0 ms-2" id="sidebarClose" type="button" style="text-decoration:none;">
                <i class="fas fa-times fa-lg"></i>
            </button>
        </div>
        
        <!-- Profile and back to website removed as per request -->
        
        <nav class="nav flex-column admin-sidebar">
            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
            
            <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            
            <hr class="bg-light opacity-25 my-2">
            <h6 class="px-3">Content</h6>
            
            <a href="announcements.php" class="nav-link <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
            <a href="opportunities.php" class="nav-link <?php echo $current_page == 'opportunities.php' ? 'active' : ''; ?>">
                <i class="fas fa-briefcase"></i> Opportunities
            </a>
            <a href="events.php" class="nav-link <?php echo $current_page == 'events.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            
            <hr class="bg-light opacity-25 my-2">
            <h6 class="px-3">Management</h6>
            
            <a href="members.php" class="nav-link <?php echo $current_page == 'members.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Members
            </a>
            <a href="finances.php" class="nav-link <?php echo $current_page == 'finances.php' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i> Finances
            </a>
            <a href="calamity-approvals.php" class="nav-link <?php echo $current_page == 'calamity-approvals.php' ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-triangle"></i> Calamities
            </a>
            <a href="gallery.php" class="nav-link <?php echo $current_page == 'gallery.php' ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Gallery
            </a>
            
            <hr class="bg-light opacity-25 my-2">
            <h6 class="px-3">System</h6>
            
            <a href="reports.php" class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <?php if (hasPermission(PERM_SETTINGS)): ?>
            <a href="settings.php" class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
            <?php endif; ?>
            <!-- Public Site and Logout removed from sidebar -->
        </nav>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('adminSidebar');
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