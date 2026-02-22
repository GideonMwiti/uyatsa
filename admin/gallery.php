<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$conn = getDBConnection();

// List gallery items with ability to delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: gallery.php'); exit();
}

$gallery = $conn->query("SELECT g.*, u.full_name FROM gallery g LEFT JOIN users u ON g.uploaded_by = u.id ORDER BY created_at DESC");
?>
<?php include 'header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10">
            <h3 class="mb-3">Gallery Management</h3>
            <div class="row">
                <?php while ($img = $gallery->fetch_assoc()): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="../assets/uploads/gallery/<?php echo $img['image_path']; ?>" class="img-fluid" />
                        <div class="card-body">
                            <h6><?php echo htmlspecialchars($img['title']); ?></h6>
                            <p class="small">By: <?php echo htmlspecialchars($img['full_name'] ?? 'N/A'); ?></p>
                            <a href="gallery.php?delete=<?php echo $img['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this image?')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
