<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread' => 0]);
    exit();
}
$userId = $_SESSION['user_id'];
$unread = countUnreadNotifications($userId);
echo json_encode(['unread' => $unread]);
