<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}
$userId = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { echo json_encode(['success' => false]); exit(); }
$ok = markNotificationAsRead($id, $userId);
echo json_encode(['success' => (bool)$ok]);
