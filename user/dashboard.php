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
    <style>
        body {
            background-color: #f0f2f5;
        }
        
        .member-stats {
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .member-stats:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .member-stats i {
            font-size: 2rem;
            margin-bottom: 10px;
            opacity: 0.8;
        }
        
        .member-stats h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            font-family: var(--font-heading);
        }
        
        .member-stats h5 {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            color: rgba(255, 255, 255, 0.9);
        }

        .bg-stat-1 { background: linear-gradient(135deg, var(--corporate-navy) 0%, #1e3c72 100%); }
        .bg-stat-2 { background: linear-gradient(135deg, var(--corporate-accent) 0%, #f39c12 100%); }
        .bg-stat-3 { background: linear-gradient(135deg, #112240 0%, #2980b9 100%); }
        .bg-stat-4 { background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%); }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--corporate-border);
            padding: 15px 20px;
            font-weight: 700;
            color: var(--corporate-navy);
        }
        
        .opportunity-item, .announcement-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        
        .opportunity-item:last-child, .announcement-item:last-child {
            border-bottom: none;
        }
        
        .opportunity-item:hover, .announcement-item:hover {
            background-color: #f8f9fa;
        }

        .welcome-section {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            border-left: 5px solid var(--corporate-accent);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 p-0">
                <?php include 'sidebar.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
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
                        <div class="member-stats bg-stat-1">
                            <i class="fas fa-briefcase"></i>
                            <h5>My Opportunities</h5>
                            <h3><?php echo $userStats['my_opportunities']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="member-stats bg-stat-2">
                            <i class="fas fa-money-bill-wave"></i>
                            <h5>Contributions</h5>
                            <h3><?php echo $userStats['my_contributions']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="member-stats bg-stat-3">
                            <i class="fas fa-calendar-check"></i>
                            <h5>Events</h5>
                            <h3><?php echo $userStats['events_registered']; ?></h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="member-stats bg-stat-4">
                            <i class="fas fa-images"></i>
                            <h5>Gallery</h5>
                            <h3><?php echo $userStats['gallery_uploads']; ?></h3>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>