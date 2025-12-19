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
<div class="container mt-4">
    <h3>System Settings</h3>
    <?php if (!empty($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>

    <form method="post" class="row g-3">
        <div class="col-12">
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="maintenance_mode" <?php echo !empty($current['maintenance_mode']) ? 'checked' : ''; ?>>
                <span class="form-check-label">Maintenance Mode (site offline for non-admins)</span>
            </label>
        </div>

        <div class="col-12">
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="registration_open" <?php echo !isset($current['registration_open']) || $current['registration_open'] ? 'checked' : ''; ?>>
                <span class="form-check-label">Allow Public Registration</span>
            </label>
        </div>

        <div class="col-12">
            <label class="form-check">
                <input class="form-check-input" type="checkbox" name="allow_file_uploads" <?php echo !empty($current['allow_file_uploads']) ? 'checked' : ''; ?>>
                <span class="form-check-label">Allow File Uploads</span>
            </label>
        </div>

        <div class="col-md-4">
            <label class="form-label">Items per page</label>
            <input type="number" name="items_per_page" class="form-control" min="1" value="<?php echo (int)($current['items_per_page'] ?? 10); ?>">
        </div>

        <div class="col-md-8">
            <label class="form-label">Site Title</label>
            <input type="text" name="site_title" class="form-control" value="<?php echo htmlspecialchars($current['site_title'] ?? 'UYTSA Community System'); ?>">
        </div>

        <div class="col-12 text-end">
            <button class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
