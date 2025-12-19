<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/constants.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    $eventId = (int)$_POST['event_id'];
    // prevent duplicate registration
    $stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $eventId, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO event_registrations (event_id, user_id) VALUES (?, ?)");
        $ins->bind_param('ii', $eventId, $userId);
        if ($ins->execute()) {
            $message = 'Registered successfully.';
        } else {
            $error = 'Failed to register.';
        }
    } else {
        $error = 'You have already registered for this event.';
    }
}

$events = $conn->query("SELECT e.*, u.full_name FROM events e LEFT JOIN users u ON e.organizer_id = u.id ORDER BY start_date DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .event-card { border-left: 4px solid #f39c12; }
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Events</h4>
                </div>
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

                <div class="row">
                    <?php while ($event = $events->fetch_assoc()): ?>
                    <?php
                        // check registration
                        $stmt = $conn->prepare("SELECT id, status FROM event_registrations WHERE event_id = ? AND user_id = ?");
                        $stmt->bind_param('ii', $event['id'], $userId);
                        $stmt->execute();
                        $reg = $stmt->get_result()->fetch_assoc();
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card event-card">
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p class="small text-muted"><?php echo date('M d, Y H:i', strtotime($event['start_date'])); ?> • <?php echo htmlspecialchars($event['location']); ?></p>
                                <p><?php echo htmlspecialchars(substr($event['description'], 0, 160)); ?>...</p>
                                <p class="small">Organizer: <?php echo htmlspecialchars($event['full_name'] ?? 'N/A'); ?></p>
                                <div class="d-flex gap-2">
                                    <?php if ($reg): ?>
                                        <button class="btn btn-sm btn-success" disabled><i class="fas fa-check"></i> Registered</button>
                                    <?php else: ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button class="btn btn-sm btn-primary" name="register_event">Register</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="events.php?view=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-secondary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
