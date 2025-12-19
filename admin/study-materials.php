<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$conn = getDBConnection();

// upload
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['upload_material'])) {
    if (!empty($_FILES['material']['name'])) {
        $res = uploadFile($_FILES['material'], UPLOAD_PATH . 'materials/', ['pdf','doc','docx','ppt','pptx','jpg','png']);
        if (!empty($res['error'])) { $error = $res['error']; }
        else {
            $path = 'assets/uploads/materials/' . $res['filename'];
            $stmt = $conn->prepare("INSERT INTO study_materials (title, description, file_path, category, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $t = sanitize($_POST['title'] ?? ''); $d = sanitize($_POST['description'] ?? ''); $c = sanitize($_POST['category'] ?? 'General');
            $stmt->bind_param('ssssi', $t,$d,$path,$c,$_SESSION['user_id']); $stmt->execute(); $message='Uploaded';
        }
    }
}

// delete
if (isset($_GET['delete'])) { $id=(int)$_GET['delete']; $conn->query("DELETE FROM study_materials WHERE id=$id"); header('Location: study-materials.php'); exit(); }

$materials = $conn->query("SELECT m.*, u.full_name FROM study_materials m LEFT JOIN users u ON m.uploaded_by = u.id ORDER BY created_at DESC");
?>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h3>Study Materials (Admin)</h3>
    <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <div class="card mb-3 p-3">
        <form method="post" enctype="multipart/form-data" class="row g-2">
            <div class="col-md-4"><input name="title" class="form-control" placeholder="Title" required></div>
            <div class="col-md-3"><input name="category" class="form-control" placeholder="Category"></div>
            <div class="col-md-3"><input type="file" name="material" class="form-control" required></div>
            <div class="col-12"><textarea name="description" class="form-control" placeholder="Description"></textarea></div>
            <div class="col-12 text-end"><button class="btn btn-primary" name="upload_material">Upload</button></div>
        </form>
    </div>

    <div class="row">
        <?php while ($m = $materials->fetch_assoc()): ?>
        <div class="col-md-6 mb-3">
            <div class="card p-3">
                <h5><?php echo htmlspecialchars($m['title']); ?></h5>
                <p class="small">By: <?php echo htmlspecialchars($m['full_name'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($m['category']); ?></p>
                <div class="text-end">
                    <a href="study-materials.php?delete=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
