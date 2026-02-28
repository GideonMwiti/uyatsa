<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_calamity'])) {
    $reporter = sanitize($_POST['reporter_name'] ?? $_SESSION['full_name']);
    $mother = sanitize($_POST['guardian_mother'] ?? '');
    $father = sanitize($_POST['guardian_father'] ?? '');
    $other = sanitize($_POST['guardian_other'] ?? '');
    $nature = sanitize($_POST['nature'] ?? '');

    if (empty($nature)) {
        $_SESSION['error'] = 'Please describe the nature of the calamity.';
        header('Location: calamity-response.php'); exit();
    } else {
        $res = createCalamity($userId, $reporter, $mother, $father, $other, $nature);
        if ($res) {
            $_SESSION['success'] = 'Calamity reported successfully. Await admin review.';
            header('Location: calamity-response.php'); exit();
        } else {
            $_SESSION['error'] = 'Failed to report calamity. Please try again.';
            header('Location: calamity-response.php'); exit();
        }
    }
}

$submissions = getCalamitiesByUser($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Calamity Response - UYTSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/shared.css" rel="stylesheet">
    <style>
        .calamity-card { border-radius: 10px; }
    </style>
    <link href="../assets/css/modern.css" rel="stylesheet">
    <link href="css/user.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="dashboard-row">
            <?php include 'sidebar.php'; ?>
            <div class="dashboard-main-content">
                 <div class="p-4 w-100">
                <h3>Calamity Response</h3>
                <?php if (!empty($message)): ?><div class="alert alert-info"><?php echo $message; ?></div><?php endif; ?>

                <div class="card p-3 mb-4 calamity-card">
                    <form method="post" class="row g-3 calamity-form">
                        <div class="col-md-6">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="reporter_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" name="guardian_mother" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="guardian_father" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Other Guardian</label>
                            <input type="text" name="guardian_other" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nature of Calamity</label>
                            <textarea name="nature" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary" name="submit_calamity">Submit Report</button>
                        </div>
                    </form>
                </div>

                <h5>Your Reports</h5>
                <div class="list-group calamity-list">
                    <?php while ($r = $submissions->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($r['nature']); ?></h6>
                                    <p class="mb-1 small">Guardian(s): <?php echo htmlspecialchars(implode(', ', array_filter([$r['guardian_mother'], $r['guardian_father'], $r['guardian_other']]))); ?></p>
                                    <?php if (!empty($r['admin_response'])): ?>
                                        <p class="mb-1"><strong>Admin Response:</strong> <?php echo htmlspecialchars($r['admin_response']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($r['amount_given']) && $r['amount_given'] > 0): ?>
                                        <p class="mb-0"><strong>Amount Given:</strong> <?php echo number_format($r['amount_given'],2); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block mb-2"><?php echo htmlspecialchars($r['created_at']); ?></small>
                                    <span class="calamity-status <?php echo 'status-' . htmlspecialchars($r['status']); ?>"><?php echo ucfirst($r['status']); ?></span>
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
</div></body>
</html>
