<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
$userId = $_SESSION['user_id'];
$notes = getNotificationsForUser($userId, 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Notifications - UYTSA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">
<link href="../assets/css/shared.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3"><?php include 'sidebar.php'; ?></div>
        <div class="col-md-9">
            <h3>Notifications</h3>
            <div class="list-group">
                <?php while ($n = $notes->fetch_assoc()): ?>
                    <a href="<?php echo htmlspecialchars($n['link'] ?: '#'); ?>" class="list-group-item list-group-item-action notification-link <?php echo $n['is_read'] ? '' : 'fw-bold'; ?>" data-notification-id="<?php echo $n['id']; ?>">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($n['title']); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($n['created_at']); ?></small>
                        </div>
                        <p class="mb-1 small text-truncate"><?php echo htmlspecialchars($n['message']); ?></p>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
