<!-- Admin Sidebar -->
<div class="col-md-2 p-0 sidebar-wrapper" style="position:relative;">
    <div class="p-3 text-white">
        <div style="position:absolute;top:0;left:100%;width:18px;height:100px;background:#fff;z-index:2;box-shadow:2px 0 8px rgba(0,0,0,0.05);border-radius:0 0 12px 12px;"></div>
        <h4 class="text-center mb-4 mt-2">
            <i class="fas fa-users-cog"></i> Admin Portal
        </h4>
        
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
            <a href="settings.php" class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
            <!-- Public Site and Logout removed from sidebar -->
        </nav>
    </div>
</div>