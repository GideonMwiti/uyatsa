<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    if (!hasPermission(PERM_FINANCES)) {
        die("Unauthorized access.");
    }

    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    $response = sanitize($_POST['admin_response'] ?? '');
    $amount = isset($_POST['amount_given']) ? (float)$_POST['amount_given'] : 0;

    // Only Treasurer may set amount; others will not change amount
    $currentRole = $_SESSION['role'] ?? '';
    $calamity = getCalamityById($id);
    $targetAmount = $calamity['amount_given'] ?? 0;
    if (strcasecmp($currentRole, 'Treasurer') === 0) {
        // treasurer may set amount (ensure non-negative)
        if ($amount < 0) { $amount = 0; }
        $targetAmount = $amount;
    }

    if ($action === 'approve') {
        updateCalamity($id, 'approved', $response, $targetAmount);
        $message = 'Calamity approved.';
        // notify user
        if (!empty($calamity['user_id'])) {
            $notifTitle = 'Calamity Approved';
            $notifMsg = 'Your calamity report has been approved.' . ($response ? " Response: $response" : '');
            if ($targetAmount > 0) { $notifMsg .= ' Amount given: Ksh ' . number_format($targetAmount,2); }
            sendCalamityNotification($calamity['user_id'], $notifTitle, $notifMsg, '/user/calamity-response.php');
        }
    } elseif ($action === 'reject') {
        updateCalamity($id, 'rejected', $response, 0);
        $message = 'Calamity rejected.';
        if (!empty($calamity['user_id'])) {
            $notifTitle = 'Calamity Rejected';
            $notifMsg = 'Your calamity report has been rejected.' . ($response ? " Reason: $response" : '');
            sendCalamityNotification($calamity['user_id'], $notifTitle, $notifMsg, '/user/calamity-response.php');
        }
    }
}

$calamities = getAllCalamities();
include 'header.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10">
            <h3 class="mb-3">Calamity Approvals</h3>
            <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>

            <div class="table-responsive">
                <table class="table table-sm calamity-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Submitted By</th>
                            <th>Nature</th>
                            <th>Guardians</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Admin Response / Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($c = $calamities->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($c['submitter'] ?? $c['reporter_name']); ?></td>
                            <td><?php echo htmlspecialchars($c['nature']); ?></td>
                            <td><?php echo htmlspecialchars(implode(', ', array_filter([$c['guardian_mother'], $c['guardian_father'], $c['guardian_other']]))); ?></td>
                            <td><?php echo htmlspecialchars($c['created_at']); ?></td>
                            <td><span class="calamity-status <?php echo 'status-' . htmlspecialchars($c['status']); ?>"><?php echo ucfirst($c['status']); ?></span></td>
                            <td>
                                <?php if (hasPermission(PERM_FINANCES)): ?>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                    <input type="text" name="admin_response" class="form-control form-control-sm" placeholder="Response" value="<?php echo htmlspecialchars($c['admin_response']); ?>">
                                    <?php $isTreasurer = (strcasecmp($_SESSION['role'] ?? '', 'Treasurer') === 0) || in_array($_SESSION['role'] ?? '', ['Patron', 'Chairperson', 'Vice_Chairperson']); ?>
                                    <input type="number" step="0.01" name="amount_given" class="form-control form-control-sm" style="max-width:120px;" value="<?php echo htmlspecialchars($c['amount_given']); ?>" <?php echo $isTreasurer ? '' : 'readonly'; ?>>
                                    <div class="btn-group">
                                        <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                        <button name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                    </div>
                                </form>
                                <?php else: ?>
                                    <span class="text-muted">View Only</span>
                                <?php endif; ?>
                            </td>
                            <td><?php if (!empty($c['admin_response'])) echo htmlspecialchars($c['admin_response']); ?><br><?php if (!empty($c['amount_given'])) echo 'Ksh '.number_format($c['amount_given'],2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
