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
    <link href="../assets/css/modern.css" rel="stylesheet">
    <link href="css/user.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
        <div class="dashboard-row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            <!-- Main Content -->
            <div class="dashboard-main-content">
                 <div class="p-4 w-100">
                <div class="welcome-section d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1 fw-bold text-navy">Member Portal</h2>
                        <p class="mb-0 text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                    </div>
                    <div class="d-none d-md-block">
                        <span class="badg bg-navy text-white px-3 py-2 rounded-pill"><?php echo date('l, F j, Y'); ?></span>
                    </div>
                </div>
                
                <!-- User Statistics -->
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white h-100 p-3" style="border-radius: 12px;">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <h5><i class="fas fa-briefcase mb-2" style="font-size: 2rem;"></i><br>My Opportunities</h5>
                                <h2 class="fw-bold m-0"><?php echo $userStats['my_opportunities']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark h-100 p-3" style="border-radius: 12px;">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <h5><i class="fas fa-money-bill-wave mb-2" style="font-size: 2rem;"></i><br>Contributions</h5>
                                <h2 class="fw-bold m-0"><?php echo $userStats['my_contributions']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white h-100 p-3" style="border-radius: 12px;">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <h5><i class="fas fa-calendar-check mb-2" style="font-size: 2rem;"></i><br>Events</h5>
                                <h2 class="fw-bold m-0"><?php echo $userStats['events_registered']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white h-100 p-3" style="border-radius: 12px;">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <h5><i class="fas fa-images mb-2" style="font-size: 2rem;"></i><br>Gallery</h5>
                                <h2 class="fw-bold m-0"><?php echo $userStats['gallery_uploads']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Announcements -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-bullhorn text-gold me-2"></i> Recent Announcements</h5>
                                <a href="announcements.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if($announcements->num_rows > 0): ?>
                                    <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                    <div class="announcement-item">
                                        <h6 class="mb-1 text-navy fw-bold"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                        <p class="small text-muted mb-1"><?php echo substr(strip_tags($announcement['content']), 0, 90) . '...'; ?></p>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="far fa-clock me-1"></i> <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?> 
                                            &bull; <?php echo htmlspecialchars($announcement['full_name']); ?>
                                        </small>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted">No recent announcements.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Opportunities -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-briefcase text-navy me-2"></i> Latest Opportunities</h5>
                                <div>
                                    <a href="opportunities.php?action=create" class="btn btn-sm btn-gold rounded-pill me-1"><i class="fas fa-plus"></i></a>
                                    <a href="opportunities.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <?php if($opportunities->num_rows > 0): ?>
                                    <?php while ($opportunity = $opportunities->fetch_assoc()): ?>
                                    <div class="opportunity-item">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 text-navy fw-bold text-truncate" style="max-width: 70%;"><?php echo htmlspecialchars($opportunity['title']); ?></h6>
                                            <span class="badge bg-light text-dark border"><?php echo ucfirst($opportunity['type']); ?></span>
                                        </div>
                                        <p class="small text-muted mb-2"><?php echo substr(strip_tags($opportunity['description']), 0, 80) . '...'; ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="far fa-user me-1"></i> <?php echo htmlspecialchars($opportunity['full_name']); ?>
                                            </small>
                                            <?php if ($opportunity['deadline']): ?>
                                                <small class="text-danger fw-bold" style="font-size: 0.75rem;">
                                                    <i class="far fa-hourglass me-1"></i> Due: <?php echo date('M d', strtotime($opportunity['deadline'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="p-4 text-center text-muted">No opportunities listed yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Upcoming Events -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt text-navy me-2"></i> Upcoming Events</h5>
                                <a href="events.php" class="btn btn-sm btn-outline-primary rounded-pill">Calendar</a>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php if($events->num_rows > 0): ?>
                                        <?php while ($event = $events->fetch_assoc()): ?>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100 position-relative bg-light">
                                                <div class="position-absolute top-0 end-0 p-3">
                                                    <span class="badge bg-gold text-white"><?php echo date('d M', strtotime($event['start_date'])); ?></span>
                                                </div>
                                                <h6 class="fw-bold text-navy mt-2 pe-5"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                <p class="small text-muted mb-2">
                                                    <i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($event['location']); ?>
                                                </small>
                                                <p class="small text-muted mb-3">
                                                    <i class="far fa-clock me-1"></i> <?php echo date('H:i', strtotime($event['start_date'])); ?>
                                                </p>
                                                <a href="events.php?view=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-dark w-100 stretched-link">Details</a>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="col-12 text-center text-muted py-3">No upcoming events scheduled.</div>
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
</body>
</html>