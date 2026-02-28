<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireExecutive();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Ensure user has permission for POST actions and delete/important
if (($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['delete']) || isset($_GET['important'])) && !hasPermission(PERM_CONTENT)) {
    die("Unauthorized access.");
}

// Handle new announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $category = sanitize($_POST['category']);
    $is_important = isset($_POST['is_important']) ? 1 : 0;
    
    $attachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $uploadResult = uploadFile($_FILES['attachment'], '../assets/uploads/announcements/', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
        if (isset($uploadResult['success'])) {
            $attachment = $uploadResult['filename'];
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, category, author_id, is_important, attachment) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiis", $title, $content, $category, $userId, $is_important, $attachment);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Announcement created successfully!';
        header('Location: announcements.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to create announcement.';
    }
}

// Handle announcement deletion
if (isset($_GET['delete'])) {
    $announcementId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $announcementId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Announcement deleted successfully!';
        header('Location: announcements.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to delete announcement.';
    }
}

// Handle mark as important
if (isset($_GET['important'])) {
    $announcementId = (int)$_GET['important'];
    $stmt = $conn->prepare("UPDATE announcements SET is_important = 1 WHERE id = ?");
    $stmt->bind_param("i", $announcementId);
    $stmt->execute();
    header('Location: announcements.php');
    exit();
}

// Get all announcements
$announcements = $conn->query("
    SELECT a.*, u.full_name 
    FROM announcements a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.is_important DESC, a.created_at DESC
");
?>
<?php include 'header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Manage Announcements</h4>
                        <?php if (hasPermission(PERM_CONTENT)): ?>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus"></i> New Announcement
                        </button>
                        <?php endif; ?>
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

                        <!-- Announcements Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Author</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($announcement = $announcements->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($announcement['title']); ?>
                                            <?php if ($announcement['is_important']): ?>
                                                <span class="badge bg-danger">Important</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($announcement['category']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($announcement['full_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></td>
                                        <td>
                                            <?php if ($announcement['is_important']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $announcement['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (hasPermission(PERM_CONTENT)): ?>
                                            <a href="?important=<?php echo $announcement['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-star"></i>
                                            </a>
                                            <a href="?delete=<?php echo $announcement['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this announcement?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $announcement['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <strong>Category:</strong> 
                                                        <span class="badge bg-info"><?php echo ucfirst($announcement['category']); ?></span>
                                                        <?php if ($announcement['is_important']): ?>
                                                            <span class="badge bg-danger ms-2">Important</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <strong>Posted by:</strong> <?php echo htmlspecialchars($announcement['full_name']); ?>
                                                        <br>
                                                        <strong>Posted on:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($announcement['created_at'])); ?>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <strong>Content:</strong>
                                                        <div class="border p-3 rounded bg-light">
                                                            <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($announcement['attachment'])): ?>
                                                        <div class="mb-3">
                                                            <strong>Attachment:</strong>
                                                            <a href="../assets/uploads/announcements/<?php echo $announcement['attachment']; ?>" 
                                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fas fa-paperclip"></i> Download Attachment
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (hasPermission(PERM_CONTENT)): ?>
    <!-- Add Announcement Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-control" required>
                                    <option value="general">General</option>
                                    <option value="academic">Academic</option>
                                    <option value="event">Event</option>
                                    <option value="financial">Financial</option>
                                    <option value="opportunity">Opportunity</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="is_important" id="is_important" class="form-check-input">
                                    <label class="form-check-label" for="is_important">
                                        Mark as Important
                                    </label>
                                    <small class="text-muted d-block">Important announcements are highlighted</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content *</label>
                            <textarea name="content" class="form-control" rows="6" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Attachment (Optional)</label>
                            <input type="file" name="attachment" class="form-control">
                            <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_announcement" class="btn btn-primary">Create Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php include 'footer.php'; ?>