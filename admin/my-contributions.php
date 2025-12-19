<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$conn = getDBConnection();

// List contributions and allow marking verified
if (isset($_GET['verify'])) {
    $id = (int)$_GET['verify'];
    $conn->query("UPDATE finances SET status='verified' WHERE id=$id");
    header('Location: my-contributions.php'); exit();
}

$contributions = $conn->query("SELECT f.*, u.full_name FROM finances f LEFT JOIN users u ON f.member_id = u.id ORDER BY recorded_at DESC");
?>
<?php include 'header.php'; ?>
<div class="container mt-4">
    <h3>Contributions (Admin)</h3>
    <div class="row">
        <div class="col-12">
            <table class="table table-sm">
                <thead><tr><th>#</th><th>Member</th><th>Amount</th><th>Purpose</th><th>Method</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php $i=1; while($r=$contributions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($r['full_name']); ?></td>
                        <td><?php echo number_format($r['amount'],2); ?></td>
                        <td><?php echo htmlspecialchars($r['purpose']); ?></td>
                        <td><?php echo htmlspecialchars($r['payment_method']); ?></td>
                        <td><?php echo htmlspecialchars($r['status']); ?></td>
                        <td>
                            <?php if ($r['status'] !== 'verified'): ?>
                                <a href="my-contributions.php?verify=<?php echo $r['id']; ?>" class="btn btn-sm btn-success">Verify</a>
                            <?php else: ?>
                                <span class="text-success">Verified</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
