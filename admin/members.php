<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireExecutive();

$conn = getDBConnection();

// Handle member update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
    $member_id = sanitize($_POST['member_id']);
    $role = sanitize($_POST['role']);
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE users SET role = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $role, $status, $member_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Member updated successfully!';
        header('Location: members.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update member.';
    }
}

// Handle member deletion
if (isset($_GET['delete'])) {
    $memberId = (int)$_GET['delete'];
    // Don't delete, just deactivate
    $stmt = $conn->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
    $stmt->bind_param("i", $memberId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Member deactivated successfully!';
        header('Location: members.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to deactivate member.';
    }
}

// Handle member activation
if (isset($_GET['activate'])) {
    $memberId = (int)$_GET['activate'];
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $memberId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Member activated successfully!';
        header('Location: members.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to activate member.';
    }
}

// Get all members with filters
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (full_name LIKE ? OR username LIKE ? OR email LIKE ? OR institution LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

if (!empty($role) && $role !== 'all') {
    $whereClause .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

if (!empty($status) && $status !== 'all') {
    $whereClause .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql = "SELECT * FROM users $whereClause ORDER BY 
        FIELD(role, 'Patron', 'Chairperson', 'Vice_Chairperson', 'Secretary_General', 'Treasurer', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket', 'member'), 
        full_name";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$members = $stmt->get_result();

// Get member statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_members,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members,
        SUM(CASE WHEN status = 'alumni' THEN 1 ELSE 0 END) as alumni_members,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_members,
        SUM(CASE WHEN role != 'member' THEN 1 ELSE 0 END) as executive_members
    FROM users
")->fetch_assoc();
?>
<?php include 'header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <div class="col-md-10">
                <!-- Member Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5>Total Members</h5>
                                <h2><?php echo $stats['total_members']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5>Active</h5>
                                <h2><?php echo $stats['active_members']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5>Executive</h5>
                                <h2><?php echo $stats['executive_members']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <h5>Alumni</h5>
                                <h2><?php echo $stats['alumni_members']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Manage Members</h4>
                        <a href="register.php" class="btn btn-light btn-sm">
                            <i class="fas fa-user-plus"></i> Add Member
                        </a>
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
                                    <input type="text" name="search" class="form-control" placeholder="Search members..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="role" class="form-control">
                                        <option value="all">All Roles</option>
                                        <option value="member" <?php echo $role == 'member' ? 'selected' : ''; ?>>Member</option>
                                        <option value="Patron" <?php echo $role == 'Patron' ? 'selected' : ''; ?>>Patron</option>
                                        <option value="Chairperson" <?php echo $role == 'Chairperson' ? 'selected' : ''; ?>>Chairperson</option>
                                        <option value="Vice_Chairperson" <?php echo $role == 'Vice_Chairperson' ? 'selected' : ''; ?>>Vice Chairperson</option>
                                        <option value="Secretary_General" <?php echo $role == 'Secretary_General' ? 'selected' : ''; ?>>Secretary General</option>
                                        <option value="Treasurer" <?php echo $role == 'Treasurer' ? 'selected' : ''; ?>>Treasurer</option>
                                        <option value="Organizing_Secretary" <?php echo $role == 'Organizing_Secretary' ? 'selected' : ''; ?>>Organizing Secretary</option>
                                        <option value="Publicity_Officer" <?php echo $role == 'Publicity_Officer' ? 'selected' : ''; ?>>Publicity Officer</option>
                                        <option value="NextGen_Docket" <?php echo $role == 'NextGen_Docket' ? 'selected' : ''; ?>>NextGen Docket</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="all">All Status</option>
                                        <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="alumni" <?php echo $status == 'alumni' ? 'selected' : ''; ?>>Alumni</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>

                        <!-- Members Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Institution</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($member = $members->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($member['profile_image'])): ?>
                                                    <img src="../assets/uploads/profile/<?php echo $member['profile_image']; ?>" 
                                                         class="rounded-circle me-2" width="40" height="40" alt="Profile">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($member['full_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($member['phone'] ?? 'No phone'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($member['username']); ?></td>
                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td><?php echo htmlspecialchars($member['institution'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php 
                                            $roleBadge = [
                                                'member' => 'secondary',
                                                'Patron' => 'danger',
                                                'Chairperson' => 'primary',
                                                'Vice_Chairperson' => 'info',
                                                'Secretary_General' => 'success',
                                                'Treasurer' => 'warning',
                                                'Organizing_Secretary' => 'info',
                                                'Publicity_Officer' => 'info',
                                                'NextGen_Docket' => 'info'
                                            ];
                                            $roleText = str_replace('_', ' ', $member['role']);
                                            ?>
                                            <span class="badge bg-<?php echo $roleBadge[$member['role']]; ?>">
                                                <?php echo $roleText; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusBadge = [
                                                'active' => 'success',
                                                'inactive' => 'danger',
                                                'alumni' => 'warning'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $statusBadge[$member['status']]; ?>">
                                                <?php echo ucfirst($member['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M Y', strtotime($member['registration_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $member['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $member['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($member['status'] == 'active'): ?>
                                                <a href="?delete=<?php echo $member['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this member?')">
                                                    <i class="fas fa-user-slash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?activate=<?php echo $member['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Activate this member?')">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $member['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Member Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-4 text-center">
                                                            <?php if (!empty($member['profile_image'])): ?>
                                                                <img src="../assets/uploads/profile/<?php echo $member['profile_image']; ?>" 
                                                                     class="rounded-circle mb-3" width="150" height="150" alt="Profile">
                                                            <?php else: ?>
                                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mb-3 mx-auto" 
                                                                     style="width: 150px; height: 150px;">
                                                                    <i class="fas fa-user text-white" style="font-size: 60px;"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <h4><?php echo htmlspecialchars($member['full_name']); ?></h4>
                                                            <p class="text-muted"><?php echo htmlspecialchars($member['username']); ?></p>
                                                            
                                                            <div class="row mt-3">
                                                                <div class="col-md-6">
                                                                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($member['email']); ?></p>
                                                                    <p><strong>Phone:</strong><br><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></p>
                                                                    <p><strong>Role:</strong><br>
                                                                        <span class="badge bg-<?php echo $roleBadge[$member['role']]; ?>">
                                                                            <?php echo $roleText; ?>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Institution:</strong><br><?php echo htmlspecialchars($member['institution'] ?? 'N/A'); ?></p>
                                                                    <p><strong>Course:</strong><br><?php echo htmlspecialchars($member['course'] ?? 'N/A'); ?></p>
                                                                    <p><strong>Year:</strong><br><?php echo htmlspecialchars($member['year_of_study'] ?? 'N/A'); ?></p>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mt-3">
                                                                <strong>Bio:</strong>
                                                                <p class="border p-2 rounded"><?php echo nl2br(htmlspecialchars($member['bio'] ?? 'No bio')); ?></p>
                                                            </div>
                                                            
                                                            <div class="mt-3">
                                                                <strong>Member Statistics:</strong>
                                                                <div class="row text-center mt-2">
                                                                    <div class="col-3">
                                                                        <small>Opportunities</small>
                                                                        <h6>
                                                                            <?php 
                                                                            $result = $conn->query("SELECT COUNT(*) as count FROM opportunities WHERE posted_by = {$member['id']}");
                                                                            echo $result->fetch_assoc()['count'];
                                                                            ?>
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <small>Gallery</small>
                                                                        <h6>
                                                                            <?php 
                                                                            $result = $conn->query("SELECT COUNT(*) as count FROM gallery WHERE uploaded_by = {$member['id']}");
                                                                            echo $result->fetch_assoc()['count'];
                                                                            ?>
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <small>Contributions</small>
                                                                        <h6>
                                                                            <?php 
                                                                            $result = $conn->query("SELECT COUNT(*) as count FROM finances WHERE member_id = {$member['id']}");
                                                                            echo $result->fetch_assoc()['count'];
                                                                            ?>
                                                                        </h6>
                                                                    </div>
                                                                    <div class="col-3">
                                                                        <small>Last Login</small>
                                                                        <h6>
                                                                            <?php 
                                                                            echo $member['last_login'] ? date('M d, Y', strtotime($member['last_login'])) : 'Never';
                                                                            ?>
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $member['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Member</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Full Name</label>
                                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['full_name']); ?>" disabled>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Role *</label>
                                                            <select name="role" class="form-control" required>
                                                                <option value="member" <?php echo $member['role'] == 'member' ? 'selected' : ''; ?>>Member</option>
                                                                <option value="Patron" <?php echo $member['role'] == 'Patron' ? 'selected' : ''; ?>>Patron</option>
                                                                <option value="Chairperson" <?php echo $member['role'] == 'Chairperson' ? 'selected' : ''; ?>>Chairperson</option>
                                                                <option value="Vice_Chairperson" <?php echo $member['role'] == 'Vice_Chairperson' ? 'selected' : ''; ?>>Vice Chairperson</option>
                                                                <option value="Secretary_General" <?php echo $member['role'] == 'Secretary_General' ? 'selected' : ''; ?>>Secretary General</option>
                                                                <option value="Treasurer" <?php echo $member['role'] == 'Treasurer' ? 'selected' : ''; ?>>Treasurer</option>
                                                                <option value="Organizing_Secretary" <?php echo $member['role'] == 'Organizing_Secretary' ? 'selected' : ''; ?>>Organizing Secretary</option>
                                                                <option value="Publicity_Officer" <?php echo $member['role'] == 'Publicity_Officer' ? 'selected' : ''; ?>>Publicity Officer</option>
                                                                <option value="NextGen_Docket" <?php echo $member['role'] == 'NextGen_Docket' ? 'selected' : ''; ?>>NextGen Docket</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Status *</label>
                                                            <select name="status" class="form-control" required>
                                                                <option value="active" <?php echo $member['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                <option value="inactive" <?php echo $member['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                <option value="alumni" <?php echo $member['status'] == 'alumni' ? 'selected' : ''; ?>>Alumni</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_member" class="btn btn-primary">Update Member</button>
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

<?php include 'footer.php'; ?>