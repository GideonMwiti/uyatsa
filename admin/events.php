<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$conn = getDBConnection();

// Create event
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['create_event'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $location = sanitize($_POST['location']);
    $organizer = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO events (title, description, start_date, end_date, location, organizer_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssi', $title, $description, $start, $end, $location, $organizer);
    $stmt->execute();
    header('Location: events.php'); exit();
}

// Delete
if (isset($_GET['delete'])) { $id=(int)$_GET['delete']; $conn->query("DELETE FROM events WHERE id=$id"); header('Location: events.php'); exit(); }

$events = $conn->query("SELECT e.*, u.full_name FROM events e LEFT JOIN users u ON e.organizer_id = u.id ORDER BY start_date DESC");
?>
<?php include 'header.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10">
            <h3 class="mb-3">Events Management</h3>
            <div class="card mb-3 p-3">
                <form method="post" class="row g-2">
                    <div class="col-md-4"><input name="title" class="form-control" placeholder="Title" required></div>
                    <div class="col-md-3"><input type="datetime-local" name="start_date" class="form-control" required></div>
                    <div class="col-md-3"><input type="datetime-local" name="end_date" class="form-control"></div>
                    <div class="col-md-2"><input name="location" class="form-control" placeholder="Location"></div>
                    <div class="col-12"><textarea name="description" class="form-control" placeholder="Description"></textarea></div>
                    <div class="col-12 text-end"><button class="btn btn-primary" name="create_event">Create Event</button></div>
                </form>
            </div>

            <div class="row">
                <?php while ($e = $events->fetch_assoc()): ?>
                <div class="col-md-6 mb-3">
                    <div class="card p-3">
                        <h5><?php echo htmlspecialchars($e['title']); ?></h5>
                        <p class="small"><?php echo date('M d, Y H:i', strtotime($e['start_date'])); ?> • <?php echo htmlspecialchars($e['location']); ?></p>
                        <p><?php echo htmlspecialchars(substr($e['description'],0,200)); ?></p>
                        <div class="text-end">
                            <a href="events.php?delete=<?php echo $e['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete event?')">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
