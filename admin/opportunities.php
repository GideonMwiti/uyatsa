  <?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireExecutive();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle opportunity verification
if (isset($_GET['verify'])) {
    $opportunityId = (int)$_GET['verify'];
    $stmt = $conn->prepare("UPDATE opportunities SET is_verified = 1 WHERE id = ?");
    $stmt->bind_param("i", $opportunityId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Opportunity verified successfully!';
        header('Location: opportunities.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to verify opportunity.';
    }
}

// Handle opportunity deletion
if (isset($_GET['delete'])) {
    $opportunityId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM opportunities WHERE id = ?");
    $stmt->bind_param("i", $opportunityId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Opportunity deleted successfully!';
        header('Location: opportunities.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to delete opportunity.';
    }
}

// Handle opportunity edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_opportunity'])) {
    $opportunity_id = sanitize($_POST['opportunity_id']);
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $type = sanitize($_POST['type']);
    $organization = sanitize($_POST['organization']);
    $deadline = $_POST['deadline'];
    $requirements = sanitize($_POST['requirements']);
    $link = sanitize($_POST['link']);
    $contact_email = sanitize($_POST['contact_email']);
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE opportunities SET title = ?, description = ?, type = ?, organization = ?, deadline = ?, requirements = ?, link = ?, contact_email = ?, is_verified = ? WHERE id = ?");
    $stmt->bind_param("ssssssssii", $title, $description, $type, $organization, $deadline, $requirements, $link, $contact_email, $is_verified, $opportunity_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Opportunity updated successfully!';
        header('Location: opportunities.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update opportunity.';
    }
}

// Get all opportunities with filters
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$verification = $_GET['verification'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (o.title LIKE ? OR o.description LIKE ? OR o.organization LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if (!empty($type) && $type !== 'all') {
    $whereClause .= " AND o.type = ?";
    $params[] = $type;
    $types .= "s";
}

if ($verification === 'pending') {
    $whereClause .= " AND o.is_verified = 0";
} elseif ($verification === 'verified') {
    $whereClause .= " AND o.is_verified = 1";
}

$sql = "SELECT o.*, u.full_name, u.profile_image 
        FROM opportunities o 
        JOIN users u ON o.posted_by = u.id 
        $whereClause 
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$opportunities = $stmt->get_result();

// Get opportunity statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_opportunities,
        SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified_opportunities,
        SUM(CASE WHEN is_verified = 0 THEN 1 ELSE 0 END) as pending_opportunities,
        SUM(views) as total_views
    FROM opportunities
")->fetch_assoc();
?>
<?php include 'header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <div class="col-md-10">
                <!-- Opportunity Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Opportunities</h5>
                                <h2><?php echo $stats['total_opportunities']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Verified</h5>
                                <h2><?php echo $stats['verified_opportunities']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h5>Pending</h5>
                                <h2><?php echo $stats['pending_opportunities']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Total Views</h5>
                                <h2><?php echo $stats['total_views']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Manage Opportunities</h4>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addOpportunityModal">
                            <i class="fas fa-plus"></i> Add Opportunity
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
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Search opportunities..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
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
                                <div class="col-md-3">
                                    <select name="verification" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo $verification == 'pending' ? 'selected' : ''; ?>>Pending Verification</option>
                                        <option value="verified" <?php echo $verification == 'verified' ? 'selected' : ''; ?>>Verified Only</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>

                        <!-- Opportunities Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Organization</th>
                                        <th>Posted By</th>
                                        <th>Views</th>
                                        <th>Status</th>
                                        <th>Posted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($opp = $opportunities->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($opp['title']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($opp['type']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($opp['organization']); ?></td>
                                        <td><?php echo htmlspecialchars($opp['full_name']); ?></td>
                                        <td><?php echo $opp['views']; ?></td>
                                        <td>
                                            <?php if ($opp['is_verified']): ?>
                                                <span class="badge bg-success">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($opp['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $opp['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $opp['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (!$opp['is_verified']): ?>
                                                <a href="?verify=<?php echo $opp['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Verify this opportunity?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete=<?php echo $opp['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this opportunity?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $opp['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Opportunity Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <h4><?php echo htmlspecialchars($opp['title']); ?></h4>
                                                        <span class="badge bg-info"><?php echo ucfirst($opp['type']); ?></span>
                                                        <?php if ($opp['is_verified']): ?>
                                                            <span class="badge bg-success ms-2">Verified</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning ms-2">Pending Verification</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p><strong>Organization:</strong> <?php echo htmlspecialchars($opp['organization']); ?></p>
                                                            <?php if ($opp['deadline']): ?>
                                                                <p><strong>Deadline:</strong> <?php echo date('F j, Y', strtotime($opp['deadline'])); ?></p>
                                                            <?php endif; ?>
                                                            <?php if ($opp['contact_email']): ?>
                                                                <p><strong>Contact Email:</strong> <?php echo htmlspecialchars($opp['contact_email']); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Posted by:</strong> <?php echo htmlspecialchars($opp['full_name']); ?></p>
                                                            <p><strong>Posted on:</strong> <?php echo date('F j, Y', strtotime($opp['created_at'])); ?></p>
                                                            <p><strong>Views:</strong> <?php echo $opp['views']; ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <div class="border p-3 rounded bg-light">
                                                            <?php echo nl2br(htmlspecialchars($opp['description'])); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($opp['requirements'])): ?>
                                                        <div class="mb-3">
                                                            <strong>Requirements:</strong>
                                                            <div class="border p-3 rounded bg-light">
                                                                <?php echo nl2br(htmlspecialchars($opp['requirements'])); ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($opp['link'])): ?>
                                                        <div class="mb-3">
                                                            <strong>Application Link:</strong>
                                                            <a href="<?php echo htmlspecialchars($opp['link']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-external-link-alt"></i> Visit Application Page
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $opp['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Opportunity</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="opportunity_id" value="<?php echo $opp['id']; ?>">
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Title *</label>
                                                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($opp['title']); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Type *</label>
                                                                <select name="type" class="form-control" required>
                                                                    <option value="internship" <?php echo $opp['type'] == 'internship' ? 'selected' : ''; ?>>Internship</option>
                                                                    <option value="scholarship" <?php echo $opp['type'] == 'scholarship' ? 'selected' : ''; ?>>Scholarship</option>
                                                                    <option value="job" <?php echo $opp['type'] == 'job' ? 'selected' : ''; ?>>Job</option>
                                                                    <option value="volunteer" <?php echo $opp['type'] == 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                                                                    <option value="training" <?php echo $opp['type'] == 'training' ? 'selected' : ''; ?>>Training</option>
                                                                    <option value="competition" <?php echo $opp['type'] == 'competition' ? 'selected' : ''; ?>>Competition</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Organization *</label>
                                                            <input type="text" name="organization" class="form-control" value="<?php echo htmlspecialchars($opp['organization']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Deadline</label>
                                                                <input type="date" name="deadline" class="form-control" value="<?php echo $opp['deadline']; ?>">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Contact Email</label>
                                                                <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($opp['contact_email']); ?>">
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Description *</label>
                                                            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($opp['description']); ?></textarea>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Requirements</label>
                                                            <textarea name="requirements" class="form-control" rows="3"><?php echo htmlspecialchars($opp['requirements']); ?></textarea>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Application Link</label>
                                                            <input type="url" name="link" class="form-control" value="<?php echo htmlspecialchars($opp['link']); ?>">
                                                        </div>
                                                        
                                                        <div class="form-check mb-3">
                                                            <input type="checkbox" name="is_verified" id="is_verified<?php echo $opp['id']; ?>" class="form-check-input" <?php echo $opp['is_verified'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="is_verified<?php echo $opp['id']; ?>">
                                                                Verified Opportunity
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="edit_opportunity" class="btn btn-primary">Update Opportunity</button>
                                                    </div>
                                                </form>
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

    <!-- Add Opportunity Modal -->
    <div class="modal fade" id="addOpportunityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Opportunity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="../user/opportunities.php?action=create">
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
                        <button type="submit" class="btn btn-primary">Add Opportunity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>