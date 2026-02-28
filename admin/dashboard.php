<?php include 'header.php'; ?>
<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireExecutive();

$conn = getDBConnection();

// Get statistics
$stats = [
    'total_members' => 0,
    'total_announcements' => 0,
    'total_opportunities' => 0,
    'total_finances' => 0,
    'pending_opportunities' => 0,
    'total_events' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_members'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM announcements");
$stats['total_announcements'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM opportunities");
$stats['total_opportunities'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM finances");
$stats['total_finances'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM opportunities WHERE is_verified = 0");
$stats['pending_opportunities'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM events");
$stats['total_events'] = $result->fetch_assoc()['count'];

// Calamity counts
ensureCalamitiesTable();
$res = $conn->query("SELECT COUNT(*) as c FROM calamities WHERE status = 'pending'");
$pendingCalamities = $res ? (int)$res->fetch_assoc()['c'] : 0;
$res = $conn->query("SELECT COUNT(*) as c FROM calamities WHERE status = 'approved'");
$approvedCalamities = $res ? (int)$res->fetch_assoc()['c'] : 0;

// Get recent activities
$recentActivities = getRecentActivities(5);

// Get executive members
$executives = $conn->query("SELECT id, full_name, role, email, phone FROM users WHERE role IN ('Patron', 'Chairperson', 'Vice_Chairperson', 'Secretary_General', 'Treasurer', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket') ORDER BY FIELD(role, 'Chairperson', 'Vice_Chairperson', 'Secretary_General', 'Treasurer', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket', 'Patron')");
?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">Welcome, Admin</h2>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-users"></i> Total Members</h5>
                                <h2><?php echo $stats['total_members']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-bullhorn"></i> Announcements</h5>
                                <h2><?php echo $stats['total_announcements']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-briefcase"></i> Opportunities</h5>
                                <h2><?php echo $stats['total_opportunities']; ?></h2>
                                <small>Pending: <?php echo $stats['pending_opportunities']; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <h5><i class="fas fa-money-bill-wave"></i> Finances</h5>
                                <h2><?php echo $stats['total_finances']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calamity summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-danger text-white">
                            <div class="card-body">
                                <h6><i class="fas fa-exclamation-triangle"></i> Pending Calamities</h6>
                                <h3><?php echo $pendingCalamities; ?></h3>
                                <a href="calamity-approvals.php" class="stretched-link text-white">Review</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body">
                                <h6><i class="fas fa-check-circle"></i> Approved Calamities</h6>
                                <h3><?php echo $approvedCalamities; ?></h3>
                                <a href="calamity-approvals.php" class="stretched-link text-white">View</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Activities</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($recentActivities as $activity): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="fas fa-<?php echo $activity['type'] == 'announcement' ? 'bullhorn' : 'briefcase'; ?> text-primary"></i>
                                                <?php echo $activity['title']; ?>
                                            </h6>
                                            <small><?php echo date('M d, H:i', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo ucfirst($activity['type']); ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Executive Panel -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Executive Panel</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php while ($executive = $executives->fetch_assoc()): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0"><?php echo $executive['full_name']; ?></h6>
                                                <small class="text-muted"><?php echo str_replace('_', ' ', $executive['role']); ?></small>
                                                <br>
                                                <small><?php echo $executive['email']; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <?php if (hasPermission(PERM_CONTENT)): ?>
                                    <a href="announcements.php?action=create" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> New Announcement
                                    </a>
                                    <a href="opportunities.php?action=create" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Add Opportunity
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (hasPermission(PERM_FINANCES)): ?>
                                    <a href="finances.php?action=create" class="btn btn-warning">
                                        <i class="fas fa-money-bill"></i> Record Transaction
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<?php include 'footer.php'; ?>