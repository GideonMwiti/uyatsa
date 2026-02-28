<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../config/constants.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Handle new contribution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contribution'])) {
    $amount = (float)$_POST['amount'];
    $purpose = sanitize($_POST['purpose'] ?? '');
    $payment_method = sanitize($_POST['payment_method'] ?? 'cash');

    $stmt = $conn->prepare("INSERT INTO finances (transaction_type, member_id, amount, purpose, payment_method, status, recorded_by, transaction_date) VALUES ('contribution', ?, ?, ?, ?, 'pending', ?, CURDATE())");
    $stmt->bind_param('idssi', $userId, $amount, $purpose, $payment_method, $userId);
    if ($stmt->execute()) {
        $message = 'Contribution recorded (pending verification).';
    } else {
        $error = 'Failed to record contribution.';
    }
}

$contributions = $conn->query("SELECT * FROM finances WHERE member_id = $userId ORDER BY recorded_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Contributions - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern.css" rel="stylesheet">
    <link href="css/user.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="dashboard-row">
            <?php include 'sidebar.php'; ?>
            <div class="dashboard-main-content">
                 <div class="p-4 w-100">
                <h4>My Contributions</h4>
                <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Record Contribution</h5>
                        <form method="post" class="row g-2">
                            <div class="col-md-3">
                                <input class="form-control" type="number" step="0.01" name="amount" placeholder="Amount" required>
                            </div>
                            <div class="col-md-5">
                                <input class="form-control" type="text" name="purpose" placeholder="Purpose">
                            </div>
                            <div class="col-md-3">
                                <select name="payment_method" class="form-control">
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" name="add_contribution">Add Contribution</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5>Your Contributions</h5>
                        <?php if ($contributions->num_rows === 0): ?>
                            <div class="alert alert-info">No contributions yet.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr><th>#</th><th>Amount</th><th>Purpose</th><th>Method</th><th>Status</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while ($row = $contributions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo number_format($row['amount'],2); ?></td>
                                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($row['payment_method'])); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'verified'): ?>
                                            <span class="badge bg-success">Verified</span>
                                        <?php elseif ($row['status'] === 'completed'): ?>
                                            <span class="badge bg-info">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</div></body>
</html>
