<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $category = sanitize($_POST['category']);
    $event_date = $_POST['event_date'];
    $location = sanitize($_POST['location']);
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadResult = uploadFile($_FILES['image'], '../assets/uploads/gallery/', ['jpg', 'jpeg', 'png', 'gif']);
        
        if (isset($uploadResult['success'])) {
            $imagePath = $uploadResult['filename'];
            
            $stmt = $conn->prepare("INSERT INTO gallery (title, description, image_path, category, uploaded_by, event_date, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiss", $title, $description, $imagePath, $category, $userId, $event_date, $location);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Image uploaded successfully!';
                header('Location: gallery.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to save gallery entry.';
            }
        } else {
            $_SESSION['error'] = $uploadResult['error'];
        }
    } else {
        $_SESSION['error'] = 'Please select an image to upload.';
    }
}

// Handle like
if (isset($_GET['like'])) {
    $galleryId = (int)$_GET['like'];
    $stmt = $conn->prepare("UPDATE gallery SET likes = likes + 1 WHERE id = ?");
    $stmt->bind_param("i", $galleryId);
    $stmt->execute();
    header('Location: gallery.php');
    exit();
}

// Get gallery images with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$category = $_GET['category'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($category) && $category !== 'all') {
    $whereClause .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql = "SELECT g.*, u.full_name, u.profile_image 
        FROM gallery g 
        JOIN users u ON g.uploaded_by = u.id 
        $whereClause 
        ORDER BY g.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$gallery = $stmt->get_result();

// Count total
$countSql = "SELECT COUNT(*) as total FROM gallery $whereClause";
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
$totalImages = $totalResult['total'];
$totalPages = ceil($totalImages / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gallery-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        
        .gallery-img:hover {
            transform: scale(1.05);
        }
        
        .gallery-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <?php include 'sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <!-- Upload Form (Modal Trigger) -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload"></i> Upload Memory
                        </button>
                        <a href="?category=all" class="btn btn-outline-primary">All</a>
                        <a href="?category=event" class="btn btn-outline-success">Events</a>
                        <!-- Expeditions category removed -->
                        <a href="?category=volunteering" class="btn btn-outline-info">Volunteering</a>
                        <a href="?category=meeting" class="btn btn-outline-secondary">Meetings</a>
                    </div>
                </div>

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

                <!-- Gallery Grid -->
                <div class="row">
                    <?php if ($gallery->num_rows > 0): ?>
                        <?php while ($image = $gallery->fetch_assoc()): ?>
                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="card gallery-card">
                                    <img src="../assets/uploads/gallery/<?php echo $image['image_path']; ?>" 
                                         class="gallery-img" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imageModal<?php echo $image['id']; ?>">
                                    
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h6>
                                        <p class="card-text small"><?php echo substr(htmlspecialchars($image['description']), 0, 100); ?>...</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($image['full_name']); ?>
                                            </small>
                                            <div>
                                                <span class="badge bg-info"><?php echo ucfirst($image['category']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 d-flex justify-content-between">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($image['event_date'])); ?>
                                            </small>
                                            <div>
                                                <a href="?like=<?php echo $image['id']; ?>" class="text-danger">
                                                    <i class="fas fa-heart"></i> <?php echo $image['likes']; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Image Modal -->
                                <div class="modal fade" id="imageModal<?php echo $image['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?php echo htmlspecialchars($image['title']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="../assets/uploads/gallery/<?php echo $image['image_path']; ?>" 
                                                     class="img-fluid rounded" 
                                                     alt="<?php echo htmlspecialchars($image['title']); ?>">
                                                
                                                <div class="mt-3 text-start">
                                                    <p><?php echo nl2br(htmlspecialchars($image['description'])); ?></p>
                                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($image['location']); ?></p>
                                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($image['event_date'])); ?></p>
                                                    <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($image['full_name']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-images fa-3x mb-3"></i>
                                <h5>No images in gallery yet</h5>
                                <p>Be the first to share a memory from UYTSA events!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Memory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Image Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                    <select name="category" class="form-control" required>
                                        <option value="event">Event</option>
                                        <option value="volunteering">Volunteering</option>
                                        <option value="meeting">Meeting</option>
                                        <option value="other">Other</option>
                                    </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g., Ulumbi Town Hall">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image *</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Max size: 20MB. Allowed: JPG, PNG, GIF</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="upload" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>