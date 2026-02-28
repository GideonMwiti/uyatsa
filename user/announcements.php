<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Get all announcements
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search and filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (a.title LIKE ? OR a.content LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if (!empty($category) && $category !== 'all') {
    $whereClause .= " AND a.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql = "SELECT a.*, u.full_name, u.profile_image 
        FROM announcements a 
        JOIN users u ON a.author_id = u.id 
        $whereClause 
        ORDER BY a.is_important DESC, a.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$announcements = $stmt->get_result();

// Count total announcements
$countSql = "SELECT COUNT(*) as total FROM announcements a $whereClause";
$countStmt = $conn->prepare($countSql);
if ($types) {
    // Remove the last two characters (limit and offset types)
    $countTypes = substr($types, 0, -2);
    $countParams = array_slice($params, 0, -2);
    if ($countTypes) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
}
$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$totalAnnouncements = $totalResult['total'];
$totalPages = ceil($totalAnnouncements / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - UYTSA</title>
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
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Announcements</h4>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filter -->
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="search" class="form-control" placeholder="Search announcements..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-4">
                                    <select name="category" class="form-control">
                                        <option value="all">All Categories</option>
                                        <option value="general" <?php echo $category == 'general' ? 'selected' : ''; ?>>General</option>
                                        <option value="academic" <?php echo $category == 'academic' ? 'selected' : ''; ?>>Academic</option>
                                        <option value="event" <?php echo $category == 'event' ? 'selected' : ''; ?>>Event</option>
                                        <option value="financial" <?php echo $category == 'financial' ? 'selected' : ''; ?>>Financial</option>
                                        <option value="opportunity" <?php echo $category == 'opportunity' ? 'selected' : ''; ?>>Opportunity</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>

                        <!-- Announcements List -->
                        <?php if ($announcements->num_rows > 0): ?>
                            <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                <div class="card mb-3 <?php echo $announcement['is_important'] ? 'border-danger' : ''; ?>">
                                    <div class="card-body">
                                        <?php if ($announcement['is_important']): ?>
                                            <span class="badge bg-danger float-end">Important</span>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex align-items-start mb-3">
                                            <?php if (!empty($announcement['profile_image'])): ?>
                                                <img src="../assets/uploads/profile/<?php echo $announcement['profile_image']; ?>" 
                                                     class="rounded-circle me-3" width="50" height="50" alt="Author">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h5 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                                <small class="text-muted">
                                                    Posted by <?php echo htmlspecialchars($announcement['full_name']); ?> | 
                                                    <?php echo date('F j, Y \a\t g:i A', strtotime($announcement['created_at'])); ?> |
                                                    <span class="badge bg-info"><?php echo ucfirst($announcement['category']); ?></span>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                        
                                        <?php if (!empty($announcement['attachment'])): ?>
                                            <div class="mt-3">
                                                <a href="../assets/uploads/announcements/<?php echo $announcement['attachment']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-paperclip"></i> View Attachment
                                                </a>
                                            </div>
                                        <?php endif; ?>
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
                                                   href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h5>No announcements found</h5>
                                <p>Check back later for new announcements from the executive team.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</div></body>
</html>