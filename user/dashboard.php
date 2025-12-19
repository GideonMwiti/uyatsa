<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

// Redirect executives to admin dashboard
if (isExecutive()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Get user data
$user = getUserById($userId);

// Get user-specific statistics
$userStats = [
    'my_opportunities' => 0,
    'my_contributions' => 0,
    'events_registered' => 0,
    'gallery_uploads' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM opportunities WHERE posted_by = $userId");
$userStats['my_opportunities'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM finances WHERE member_id = $userId");
$userStats['my_contributions'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM event_registrations WHERE user_id = $userId");
$userStats['events_registered'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM gallery WHERE uploaded_by = $userId");
$userStats['gallery_uploads'] = $result->fetch_assoc()['count'];

// Get recent announcements
$announcements = $conn->query("SELECT a.*, u.full_name FROM announcements a JOIN users u ON a.author_id = u.id ORDER BY created_at DESC LIMIT 5");

// Get latest opportunities
$opportunities = $conn->query("SELECT o.*, u.full_name FROM opportunities o JOIN users u ON o.posted_by = u.id WHERE o.is_verified = 1 ORDER BY created_at DESC LIMIT 5");

// Get upcoming events
$events = $conn->query("SELECT * FROM events WHERE start_date > NOW() AND status = 'upcoming' ORDER BY start_date ASC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .member-sidebar {
            background: linear-gradient(135deg, #08203a 0%, #0f3a63 100%);
            min-height: 100vh;
            color: #f8fbff;
        }
        
        .member-stats {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            color: white;
            text-align: center;
        }
        
        .opportunity-card {
            border-left: 4px solid #3498db;
            margin-bottom: 10px;
        }
        
        .announcement-card {
            border-left: 4px solid #2ecc71;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
<div class="col-md-3 col-lg-2 p-0">
    <div class="nav flex-column bg-blue p-3 rounded member-sidebar">
        <h4 class="text-center mb-4">
            <i class="fas fa-user-circle"></i><br>
            <?php echo $_SESSION['full_name']; ?>
        </h4>

        <div class="text-center mb-4">
            <img src="<?php echo !empty($user['profile_image']) ? '../' . PROFILE_IMAGE_PATH . $user['profile_image'] : 'https://via.placeholder.com/100'; ?>" 
                 class="rounded-circle mb-2" width="100" height="100">
            <p class="mb-1"><?php echo $user['institution'] ?? 'Not specified'; ?></p>
            <span class="badge bg-light text-dark">Member</span>
        </div>

        <a href="dashboard.php" class="nav-link active">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="profile.php" class="nav-link">
            <i class="fas fa-user"></i> My Profile
        </a>
        <a href="opportunities.php" class="nav-link">
            <i class="fas fa-briefcase"></i> Opportunities
        </a>
        <a href="announcements.php" class="nav-link">
            <i class="fas fa-bullhorn"></i> Announcements
        </a>
        <a href="gallery.php" class="nav-link">
            <i class="fas fa-images"></i> Gallery
        </a>
        <a href="events.php" class="nav-link">
            <i class="fas fa-calendar-alt"></i> Events
        </a>
        <a href="study-materials.php" class="nav-link">
            <i class="fas fa-book"></i> Study Materials
        </a>
        <a href="my-contributions.php" class="nav-link">
            <i class="fas fa-money-bill-wave"></i> My Contributions
        </a>
        <a href="../logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Welcome to UYTSA Member Portal</h2>
                
                <!-- User Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="member-stats bg-primary">
                            <h5><i class="fas fa-briefcase"></i> My Opportunities</h5>
                            <h3><?php echo $userStats['my_opportunities']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="member-stats bg-success">
                            <h5><i class="fas fa-money-bill-wave"></i> Contributions</h5>
                            <h3><?php echo $userStats['my_contributions']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="member-stats bg-info">
                            <h5><i class="fas fa-calendar-check"></i> Events Registered</h5>
                            <h3><?php echo $userStats['events_registered']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="member-stats bg-warning">
                            <h5><i class="fas fa-images"></i> Gallery Uploads</h5>
                            <h3><?php echo $userStats['gallery_uploads']; ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Announcements -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Recent Announcements</h5>
                            </div>
                            <div class="card-body">
                                <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                <div class="card announcement-card">
                                    <div class="card-body">
                                        <h6><?php echo $announcement['title']; ?></h6>
                                        <p class="small"><?php echo substr($announcement['content'], 0, 100) . '...'; ?></p>
                                        <small class="text-muted">
                                            By: <?php echo $announcement['full_name']; ?> | 
                                            <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <a href="announcements.php" class="btn btn-outline-success btn-sm">View All</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Opportunities -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Latest Opportunities</h5>
                            </div>
                            <div class="card-body">
                                <?php while ($opportunity = $opportunities->fetch_assoc()): ?>
                                <div class="card opportunity-card">
                                    <div class="card-body">
                                        <h6><?php echo $opportunity['title']; ?></h6>
                                        <span class="badge bg-info"><?php echo ucfirst($opportunity['type']); ?></span>
                                        <?php if ($opportunity['deadline']): ?>
                                            <span class="badge bg-warning">Deadline: <?php echo date('M d, Y', strtotime($opportunity['deadline'])); ?></span>
                                        <?php endif; ?>
                                        <p class="small mt-2"><?php echo substr($opportunity['description'], 0, 80) . '...'; ?></p>
                                        <small class="text-muted">
                                            Posted by: <?php echo $opportunity['full_name']; ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                                <a href="opportunities.php" class="btn btn-outline-primary btn-sm">View All</a>
                                <a href="opportunities.php?action=create" class="btn btn-success btn-sm">Post Opportunity</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Events -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">Upcoming Events</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php while ($event = $events->fetch_assoc()): ?>
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6><?php echo $event['title']; ?></h6>
                                                <p class="small">
                                                    <i class="fas fa-calendar"></i> 
                                                    <?php echo date('M d, Y H:i', strtotime($event['start_date'])); ?>
                                                </p>
                                                <p class="small">
                                                    <i class="fas fa-map-marker-alt"></i> 
                                                    <?php echo $event['location']; ?>
                                                </p>
                                                <a href="events.php?view=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-warning">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <a href="events.php" class="btn btn-warning btn-sm mt-3">View All Events</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>