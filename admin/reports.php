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
<div class="container-fluid mt-4">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold">Reports Overview</h3>
                <div>
                    <button class="btn btn-outline-primary me-2" onclick="printPage('reportContent')"><i class="fas fa-print"></i> Print</button>
                    <button class="btn btn-outline-success" onclick="exportReportCSV()"><i class="fas fa-file-csv"></i> Export CSV</button>
                </div>
            </div>
            <div id="reportContent">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <h5 class="card-title">Members</h5>
                                <p class="display-6 fw-bold mb-0"><?php echo $totalMembers; ?></p>
                                <small class="text-muted">Total registered members</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-alt fa-2x text-success"></i>
                                </div>
                                <h5 class="card-title">Events</h5>
                                <p class="display-6 fw-bold mb-0"><?php echo $totalEvents; ?></p>
                                <small class="text-muted">Total events organized</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="fas fa-hand-holding-usd fa-2x text-info"></i>
                                </div>
                                <h5 class="card-title">Contributions</h5>
                                <p class="display-6 fw-bold mb-0">Ksh <?php echo number_format($totalContributions['total_amount'],2); ?></p>
                                <small class="text-muted">Total contributions received</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="mb-3">Summary</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Members:</strong> <?php echo $totalMembers; ?> registered</li>
                            <li class="list-group-item"><strong>Events:</strong> <?php echo $totalEvents; ?> organized</li>
                            <li class="list-group-item"><strong>Contributions:</strong> Ksh <?php echo number_format($totalContributions['total_amount'],2); ?> from <?php echo $totalContributions['c']; ?> transactions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function exportReportCSV() {
    const data = [
        { "Members": <?php echo $totalMembers; ?>, "Events": <?php echo $totalEvents; ?>, "Contributions": "<?php echo number_format($totalContributions['total_amount'],2); ?>" }
    ];
    let csv = 'Members,Events,Contributions\n';
    csv += `${data[0].Members},${data[0].Events},${data[0].Contributions}\n`;
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'uytsa_report.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
<?php include 'footer.php'; ?>
