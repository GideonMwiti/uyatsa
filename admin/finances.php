<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireExecutive();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Check if user has finance permissions for POST actions and delete
if (($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['delete'])) && !hasPermission(PERM_FINANCES)) {
    die("Unauthorized access.");
}

// Handle new transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $transaction_type = sanitize($_POST['transaction_type']);
    $member_id = sanitize($_POST['member_id']);
    $amount = sanitize($_POST['amount']);
    $purpose = sanitize($_POST['purpose']);
    $description = sanitize($_POST['description']);
    $receipt_number = sanitize($_POST['receipt_number']);
    $payment_method = sanitize($_POST['payment_method']);
    $status = sanitize($_POST['status']);
    $transaction_date = $_POST['transaction_date'];
    
    $stmt = $conn->prepare("INSERT INTO finances (transaction_type, member_id, amount, purpose, description, receipt_number, payment_method, status, recorded_by, transaction_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisssssis", $transaction_type, $member_id, $amount, $purpose, $description, $receipt_number, $payment_method, $status, $userId, $transaction_date);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Transaction recorded successfully!';
        header('Location: finances.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to record transaction.';
    }
}

// Handle transaction update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_transaction'])) {
    $transaction_id = sanitize($_POST['transaction_id']);
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE finances SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $transaction_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Transaction updated successfully!';
        header('Location: finances.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update transaction.';
    }
}

// Handle transaction deletion
if (isset($_GET['delete'])) {
    $transactionId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM finances WHERE id = ?");
    $stmt->bind_param("i", $transactionId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Transaction deleted successfully!';
        header('Location: finances.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to delete transaction.';
    }
}

// Get all transactions
$transactions = $conn->query("
    SELECT f.*, u.full_name as member_name, ur.full_name as recorded_by_name 
    FROM finances f 
    LEFT JOIN users u ON f.member_id = u.id 
    LEFT JOIN users ur ON f.recorded_by = ur.id 
    ORDER BY f.transaction_date DESC, f.recorded_at DESC
");

// Get total statistics
$stats = $conn->query("
    SELECT 
        SUM(CASE WHEN transaction_type IN ('contribution', 'donation', 'project_fund', 'event_fund') THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as total_expenses,
        COUNT(*) as total_transactions
    FROM finances
    WHERE status = 'completed'
")->fetch_assoc();

// Get members for dropdown
$members = $conn->query("SELECT id, full_name, username FROM users ORDER BY full_name");
?>
<?php include 'header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <div class="col-md-10">
                <!-- Financial Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-money-bill-wave"></i> Total Income</h5>
                                <h2>Ksh <?php echo number_format($stats['total_income'] ?? 0, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-receipt"></i> Total Expenses</h5>
                                <h2>Ksh <?php echo number_format($stats['total_expenses'] ?? 0, 2); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5><i class="fas fa-exchange-alt"></i> Total Transactions</h5>
                                <h2><?php echo $stats['total_transactions'] ?? 0; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Financial Transactions</h4>
                        <?php if (hasPermission(PERM_FINANCES)): ?>
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus"></i> New Transaction
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <!-- Success/Error Messages -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Transactions Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Member</th>
                                        <th>Type</th>
                                        <th>Purpose</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Recorded By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($transaction['member_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php 
                                            $typeBadge = [
                                                'contribution' => 'primary',
                                                'donation' => 'success',
                                                'expense' => 'danger',
                                                'project_fund' => 'info',
                                                'event_fund' => 'warning'
                                            ];
                                            $typeText = ucwords(str_replace('_', ' ', $transaction['transaction_type']));
                                            ?>
                                            <span class="badge bg-<?php echo $typeBadge[$transaction['transaction_type']]; ?>">
                                                <?php echo $typeText; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['purpose']); ?></td>
                                        <td class="fw-bold">
                                            Ksh <?php echo number_format($transaction['amount'], 2); ?>
                                        </td>
                                        <td><?php echo ucwords(str_replace('_', ' ', $transaction['payment_method'])); ?></td>
                                        <td>
                                            <?php 
                                            $statusBadge = [
                                                'pending' => 'warning',
                                                'completed' => 'success',
                                                'verified' => 'info',
                                                'cancelled' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $statusBadge[$transaction['status']]; ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($transaction['recorded_by_name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $transaction['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (hasPermission(PERM_FINANCES)): ?>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $transaction['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this transaction?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    
                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $transaction['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Transaction Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <strong>Transaction ID:</strong> <?php echo $transaction['id']; ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Receipt Number:</strong> <?php echo htmlspecialchars($transaction['receipt_number']); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Member:</strong> <?php echo htmlspecialchars($transaction['member_name'] ?? 'N/A'); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Type:</strong> 
                                                        <span class="badge bg-<?php echo $typeBadge[$transaction['transaction_type']]; ?>">
                                                            <?php echo $typeText; ?>
                                                        </span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Amount:</strong> 
                                                        <span class="fw-bold">Ksh <?php echo number_format($transaction['amount'], 2); ?></span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Purpose:</strong> <?php echo htmlspecialchars($transaction['purpose']); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <p class="border p-2 rounded"><?php echo nl2br(htmlspecialchars($transaction['description'])); ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Payment Method:</strong> 
                                                        <?php echo ucwords(str_replace('_', ' ', $transaction['payment_method'])); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Status:</strong> 
                                                        <span class="badge bg-<?php echo $statusBadge[$transaction['status']]; ?>">
                                                            <?php echo ucfirst($transaction['status']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Transaction Date:</strong> 
                                                        <?php echo date('F j, Y', strtotime($transaction['transaction_date'])); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Recorded By:</strong> 
                                                        <?php echo htmlspecialchars($transaction['recorded_by_name']); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Recorded At:</strong> 
                                                        <?php echo date('F j, Y \a\t g:i A', strtotime($transaction['recorded_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (hasPermission(PERM_FINANCES)): ?>
                                    <!-- Update Modal -->
                                    <div class="modal fade" id="updateModal<?php echo $transaction['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Transaction Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Current Status</label>
                                                            <input type="text" class="form-control" value="<?php echo ucfirst($transaction['status']); ?>" disabled>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">New Status *</label>
                                                            <select name="status" class="form-control" required>
                                                                <option value="pending" <?php echo $transaction['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="completed" <?php echo $transaction['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                <option value="verified" <?php echo $transaction['status'] == 'verified' ? 'selected' : ''; ?>>Verified</option>
                                                                <option value="cancelled" <?php echo $transaction['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_transaction" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (hasPermission(PERM_FINANCES)): ?>
    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record New Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transaction Type *</label>
                                <select name="transaction_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="contribution">Member Contribution</option>
                                    <option value="donation">Donation</option>
                                    <option value="expense">Expense</option>
                                    <option value="project_fund">Project Fund</option>
                                    <option value="event_fund">Event Fund</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Member (Optional)</label>
                                <select name="member_id" class="form-control">
                                    <option value="">Select Member</option>
                                    <?php while ($member = $members->fetch_assoc()): ?>
                                        <option value="<?php echo $member['id']; ?>">
                                            <?php echo htmlspecialchars($member['full_name'] . ' (' . $member['username'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount (Ksh) *</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Receipt Number (Optional)</label>
                                <input type="text" name="receipt_number" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Purpose *</label>
                            <input type="text" name="purpose" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method *</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="cash">Cash</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transaction Date *</label>
                                <input type="date" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-control" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="verified">Verified</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_transaction" class="btn btn-primary">Record Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php include 'footer.php'; ?>