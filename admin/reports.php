<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$conn = getDBConnection();

// Simple reports overview (members, events, contributions)
$totalMembers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalEvents = $conn->query("SELECT COUNT(*) as c FROM events")->fetch_assoc()['c'];
$totalContributions = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as total_amount FROM finances WHERE transaction_type='contribution'")->fetch_assoc();

?>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h3>Reports</h3>
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Members</h5>
                <p class="display-6"><?php echo $totalMembers; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Events</h5>
                <p class="display-6"><?php echo $totalEvents; ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Contributions</h5>
                <p class="display-6"><?php echo number_format($totalContributions['total_amount'],2); ?></p>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
