<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();
requireExecutive();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize and save settings
    $maintenance = isset($_POST['maintenance_mode']) ? true : false;
    $registration = isset($_POST['registration_open']) ? true : false;
    $uploads = isset($_POST['allow_file_uploads']) ? true : false;
    $items_per_page = (int)($_POST['items_per_page'] ?? 10);
    $site_title = sanitize($_POST['site_title'] ?? 'UYTSA Community System');

    setSetting('maintenance_mode', $maintenance);
    setSetting('registration_open', $registration);
    setSetting('allow_file_uploads', $uploads);
    setSetting('items_per_page', $items_per_page);
    setSetting('site_title', $site_title);

    $message = 'Settings updated successfully.';
}

$current = getAllSettings();

include 'header.php';
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold">System Settings</h3>
                <i class="fas fa-cogs fa-2x text-primary"></i>
            </div>
            <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-primary shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-wrench text-primary me-2"></i> Maintenance Mode</h5>
                            <p class="mb-2">Site offline for non-admins</p>
                            <form method="post">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="maintenance_mode" <?php echo !empty($current['maintenance_mode']) ? 'checked' : ''; ?> >
                                    <span class="form-check-label">Enable Maintenance Mode</span>
                                </label>
                                <button class="btn btn-primary mt-3">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-user-plus text-success me-2"></i> Registration</h5>
                            <p class="mb-2">Allow public registration</p>
                            <form method="post">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="registration_open" <?php echo !isset($current['registration_open']) || $current['registration_open'] ? 'checked' : ''; ?> >
                                    <span class="form-check-label">Enable Registration</span>
                                </label>
                                <button class="btn btn-success mt-3">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-info shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-upload text-info me-2"></i> File Uploads</h5>
                            <p class="mb-2">Allow file uploads in system</p>
                            <form method="post">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allow_file_uploads" <?php echo !empty($current['allow_file_uploads']) ? 'checked' : ''; ?> >
                                    <span class="form-check-label">Enable File Uploads</span>
                                </label>
                                <button class="btn btn-info mt-3">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-secondary shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-list-ol text-secondary me-2"></i> Items per Page</h5>
                            <form method="post">
                                <label class="form-label">Items per page</label>
                                <input type="number" name="items_per_page" class="form-control mb-2" min="1" value="<?php echo (int)($current['items_per_page'] ?? 10); ?>">
                                <button class="btn btn-secondary mt-2">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-dark shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-heading text-dark me-2"></i> Site Title</h5>
                            <form method="post">
                                <label class="form-label">Site Title</label>
                                <input type="text" name="site_title" class="form-control mb-2" value="<?php echo htmlspecialchars($current['site_title'] ?? 'UYTSA Community System'); ?>">
                                <button class="btn btn-dark mt-2">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
