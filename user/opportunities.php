<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle new opportunity submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_opportunity'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $type = sanitize($_POST['type']);
    $organization = sanitize($_POST['organization']);
    $deadline = $_POST['deadline'];
    $requirements = sanitize($_POST['requirements']);
    $link = sanitize($_POST['link']);
    $contact_email = sanitize($_POST['contact_email']);
    
    $stmt = $conn->prepare("INSERT INTO opportunities (title, description, type, organization, deadline, requirements, link, contact_email, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $title, $description, $type, $organization, $deadline, $requirements, $link, $contact_email, $userId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Opportunity submitted successfully! It will be visible after verification.';
        header('Location: opportunities.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to submit opportunity.';
    }
}

// Handle opportunity view increment
if (isset($_GET['view'])) {
    $oppId = (int)$_GET['view'];
    $conn->query("UPDATE opportunities SET views = views + 1 WHERE id = $oppId");
}

// Get opportunities with filters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$verifiedOnly = true; // Only show verified opportunities to regular users

$whereClause = "WHERE is_verified = 1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (title LIKE ? OR description LIKE ? OR organization LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if (!empty($type) && $type !== 'all') {
    $whereClause .= " AND type = ?";
    $params[] = $type;
    $types .= "s";
}

// If user is viewing their own opportunities
if (isset($_GET['my_opps'])) {
    $whereClause = "WHERE posted_by = ?";
    $params = [$userId];
    $types = "i";
    $verifiedOnly = false;
}

$sql = "SELECT o.*, u.full_name, u.profile_image 
        FROM opportunities o 
        JOIN users u ON o.posted_by = u.id 
        $whereClause 
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$opportunities = $stmt->get_result();

// Count total
$countSql = "SELECT COUNT(*) as total FROM opportunities $whereClause";
$countStmt = $conn->prepare($countSql);
if ($types) {
    $countTypes = substr($types, 0, -2);
    $countParams = array_slice($params, 0, -2);
    if ($countTypes) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
}
$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$totalOpps = $totalResult['total'];
$totalPages = ceil($totalOpps / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opportunities - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern.css" rel="stylesheet">
    <link href="css/user.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="dashboard-row">
            <?php include 'sidebar.php'; ?>
            <div class="dashboard-main-content">
                 <div class="p-4 w-100">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary">Total Opportunities</h6>
                                <h3 class="fw-bold"><?php echo $totalOpps; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center border-success">
                            <div class="card-body">
                                <h6 class="card-title text-success">My Posts</h6>
                                <h3 class="fw-bold">
                                    <?php
                                    $myCountStmt = $conn->prepare("SELECT COUNT(*) as total FROM opportunities WHERE posted_by = ?");
                                    $myCountStmt->bind_param("i", $userId);
                                    $myCountStmt->execute();
                                    $myCountResult = $myCountStmt->get_result()->fetch_assoc();
                                    echo $myCountResult['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center border-info">
                            <div class="card-body">
                                <h6 class="card-title text-info">Verified</h6>
                                <h3 class="fw-bold">
                                    <?php
                                    $verifiedStmt = $conn->prepare("SELECT COUNT(*) as total FROM opportunities WHERE is_verified = 1");
                                    $verifiedStmt->execute();
                                    $verifiedResult = $verifiedStmt->get_result()->fetch_assoc();
                                    echo $verifiedResult['total'];
                                    ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Opportunities Card -->
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Opportunities</h4>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#postModal">
                            <i class="fas fa-plus"></i> Post Opportunity
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Success/Error Messages -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Search and Filter -->
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="search" class="form-control" placeholder="Search opportunities..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-4">
                                    <select name="type" class="form-control">
                                        <option value="all">All Types</option>
                                        <option value="internship" <?php echo $type == 'internship' ? 'selected' : ''; ?>>Internship</option>
                                        <option value="scholarship" <?php echo $type == 'scholarship' ? 'selected' : ''; ?>>Scholarship</option>
                                        <option value="job" <?php echo $type == 'job' ? 'selected' : ''; ?>>Job</option>
                                        <option value="volunteer" <?php echo $type == 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                                        <option value="training" <?php echo $type == 'training' ? 'selected' : ''; ?>>Training</option>
                                        <option value="competition" <?php echo $type == 'competition' ? 'selected' : ''; ?>>Competition</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <a href="opportunities.php" class="btn btn-outline-primary btn-sm">All Opportunities</a>
                                <a href="?my_opps=1" class="btn btn-outline-success btn-sm">My Posts</a>
                            </div>
                        </form>

                        <!-- Opportunities List -->
                        <?php if ($opportunities->num_rows > 0): ?>
                            <?php while ($opp = $opportunities->fetch_assoc()): ?>
                                <div class="card mb-3 opportunity-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="card-title"><?php echo htmlspecialchars($opp['title']); ?></h5>
                                                <span class="badge bg-info"><?php echo ucfirst($opp['type']); ?></span>
                                                <?php if (!$opp['is_verified']): ?>
                                                    <span class="badge bg-warning">Pending Verification</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-eye"></i> <?php echo $opp['views']; ?> views
                                                </small>
                                                <small class="text-muted">
                                                    Posted: <?php echo date('M d, Y', strtotime($opp['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <p><strong>Organization:</strong> <?php echo htmlspecialchars($opp['organization']); ?></p>
                                            <?php if ($opp['deadline']): ?>
                                                <p><strong>Deadline:</strong> <span class="text-danger"><?php echo date('F j, Y', strtotime($opp['deadline'])); ?></span></p>
                                            <?php endif; ?>
                                            <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($opp['description'], 0, 200))); ?>...</p>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <?php if (!empty($opp['profile_image'])): ?>
                                                    <img src="../assets/uploads/profile/<?php echo $opp['profile_image']; ?>" 
                                                         class="rounded-circle me-2" width="30" height="30" alt="Author">
                                                <?php endif; ?>
                                                <small class="text-muted">Posted by: <?php echo htmlspecialchars($opp['full_name']); ?></small>
                                            </div>
                                            <div>
                                                <a href="opportunities.php?view=<?php echo $opp['id']; ?>" class="btn btn-sm btn-primary">
                                                    View Details
                                                </a>
                                                <?php if (!empty($opp['link'])): ?>
                                                    <a href="<?php echo htmlspecialchars($opp['link']); ?>" target="_blank" class="btn btn-sm btn-success">
                                                        Apply Now
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" 
                                                   href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type); ?><?php echo isset($_GET['my_opps']) ? '&my_opps=1' : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-briefcase fa-3x mb-3"></i>
                                <h5>No opportunities found</h5>
                                <p>Be the first to post an opportunity or check back later!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Opportunity Modal -->
    <div class="modal fade" id="postModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post New Opportunity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type *</label>
                                <select name="type" class="form-control" required>
                                    <option value="internship">Internship</option>
                                    <option value="scholarship">Scholarship</option>
                                    <option value="job">Job</option>
                                    <option value="volunteer">Volunteer</option>
                                    <option value="training">Training</option>
                                    <option value="competition">Competition</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Organization *</label>
                            <input type="text" name="organization" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deadline</label>
                                <input type="date" name="deadline" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <textarea name="requirements" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Application Link</label>
                            <input type="url" name="link" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit_opportunity" class="btn btn-primary">Submit Opportunity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</div></body>
</html>