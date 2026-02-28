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
    <link href="../assets/css/modern.css" rel="stylesheet">
    <link href="css/user.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<div class="dashboard-row">
        <?php include 'sidebar.php'; ?>
        <div class="dashboard-main-content">
                 <div class="p-4 w-100">
            <h3>Notifications</h3>
            <div class="list-group">
                <?php if (!empty($notes) && $notes->num_rows > 0): ?>
                    <?php while ($n = $notes->fetch_assoc()): ?>
                        <a href="<?php echo htmlspecialchars($n['link'] ?: '#'); ?>" class="list-group-item list-group-item-action notification-link <?php echo $n['is_read'] ? '' : 'fw-bold'; ?>" data-notification-id="<?php echo $n['id']; ?>">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($n['title']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($n['created_at']); ?></small>
                            </div>
                            <p class="mb-1 small text-truncate"><?php echo htmlspecialchars($n['message']); ?></p>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-3 text-light"></i>
                        <p class="mb-0">You have no notifications yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
