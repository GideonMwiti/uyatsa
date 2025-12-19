<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!-- User Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            <i class="fas fa-users"></i> UYTSA
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="userNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-2">
                    <?php
                    $notifCount = 0;
                    if (!empty($_SESSION['user_id'])) {
                        $notifCount = countUnreadNotifications($_SESSION['user_id']);
                        $notes = getNotificationsForUser($_SESSION['user_id'], 5);
                    }
                    ?>
                    <div class="nav-link dropdown">
                        <a class="nav-link position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger notification-badge <?php echo $notifCount ? '' : 'd-none'; ?>"><?php echo $notifCount; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:300px;">
                            <li class="dropdown-header">Notifications</li>
                            <?php if (!empty($notes)): ?>
                                <?php while($n = $notes->fetch_assoc()): ?>
                                    <li>
                                        <a class="dropdown-item notification-link <?php echo $n['is_read'] ? '' : 'fw-bold'; ?>" href="<?php echo htmlspecialchars($n['link'] ?? '#'); ?>" data-notification-id="<?php echo $n['id']; ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <div><?php echo htmlspecialchars($n['title']); ?></div>
                                                <small class="text-muted"><?php echo date('M d', strtotime($n['created_at'])); ?></small>
                                            </div>
                                            <div class="small text-truncate text-muted"><?php echo htmlspecialchars($n['message']); ?></div>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li><div class="dropdown-item small text-muted">No notifications</div></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center small" href="notifications.php">View All</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['full_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="my-contributions.php"><i class="fas fa-money-bill-wave"></i> My Contributions</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>