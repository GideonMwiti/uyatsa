<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/constants.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    if (!empty($_FILES['material_file']['name'])) {
        $res = uploadFile($_FILES['material_file'], UPLOAD_PATH . 'materials/', ['pdf','doc','docx','ppt','pptx','jpg','png']);
        if (!empty($res['error'])) {
            $error = $res['error'];
        } else {
            $filename = $res['filename'];
            $title = sanitize($_POST['title'] ?? 'Untitled');
            $description = sanitize($_POST['description'] ?? '');
            $category = sanitize($_POST['category'] ?? 'General');

            $stmt = $conn->prepare("INSERT INTO study_materials (title, description, file_path, category, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $path = 'assets/uploads/materials/' . $filename;
            $stmt->bind_param('ssssi', $title, $description, $path, $category, $userId);
            if ($stmt->execute()) {
                $message = 'Material uploaded successfully.';
            } else {
                $error = 'Failed to save material.';
            }
        }
    } else {
        $error = 'Please select a file to upload.';
    }
}

// Handle download (increment counter and serve file)
if (isset($_GET['download'])) {
    $id = (int)$_GET['download'];
    $stmt = $conn->prepare("SELECT * FROM study_materials WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $mat = $stmt->get_result()->fetch_assoc();
    if ($mat && !empty($mat['file_path'])) {
        $file = __DIR__ . '/../' . $mat['file_path'];
        if (file_exists($file)) {
            // increment
            $conn->query("UPDATE study_materials SET downloads = downloads + 1 WHERE id = $id");
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit();
        }
    }
}

$materials = $conn->query("SELECT m.*, u.full_name FROM study_materials m LEFT JOIN users u ON m.uploaded_by = u.id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Materials - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .material-card { min-height: 160px; }
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
                <h4>Study Materials</h4>
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Upload Material</h5>
                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <input class="form-control" type="text" name="title" placeholder="Title" required>
                                </div>
                                <div class="col-md-3">
                                    <input class="form-control" type="text" name="category" placeholder="Category">
                                </div>
                                <div class="col-md-4">
                                    <input type="file" name="material_file" class="form-control" required>
                                </div>
                                <div class="col-12 mt-2">
                                    <textarea class="form-control" name="description" placeholder="Description"></textarea>
                                </div>
                                <div class="col-12 mt-2">
                                    <button class="btn btn-primary" name="upload_material">Upload Material</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <?php if ($materials->num_rows > 0): ?>
                        <?php while ($m = $materials->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card material-card">
                                <div class="card-body d-flex flex-column">
                                    <h5><?php echo htmlspecialchars($m['title']); ?></h5>
                                    <p class="small text-muted">By: <?php echo htmlspecialchars($m['full_name'] ?? 'N/A'); ?> • <?php echo htmlspecialchars($m['category']); ?></p>
                                    <p class="flex-grow-1"><?php echo htmlspecialchars(substr($m['description'], 0, 120)); ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if (!empty($m['file_path'])): ?>
                                                <a class="btn btn-sm btn-outline-primary" href="study-materials.php?download=<?php echo $m['id']; ?>"><i class="fas fa-download"></i> Download</a>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">Downloads: <?php echo (int)$m['downloads']; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">No study materials found. Upload the first one.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
