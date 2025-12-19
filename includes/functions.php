<?php
require_once 'session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Sanitize input
function sanitize($input) {
    $conn = getDBConnection();
    return htmlspecialchars(strip_tags(trim($input)));
}

// Password hash
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Upload file
function uploadFile($file, $directory, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf']) {
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['error' => 'File type not allowed'];
    }
    
    if ($fileSize > MAX_FILE_SIZE) {
        return ['error' => 'File too large'];
    }
    
    if ($fileError !== 0) {
        return ['error' => 'Upload error'];
    }
    
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $fileDestination = $directory . $newFileName;
    
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }
    
    if (move_uploaded_file($fileTmp, $fileDestination)) {
        return ['success' => true, 'filename' => $newFileName];
    }
    
    return ['error' => 'Failed to upload file'];
}

// Add notification
function addNotification($userId, $title, $message, $type = 'info', $link = null) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $title, $message, $type, $link);
    
    return $stmt->execute();
}

// Get user by ID
function getUserById($id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

// Count total members
function countTotalMembers() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role != 'member' OR role = 'member'");
    return $result->fetch_assoc()['total'];
}

// Get recent activities
function getRecentActivities($limit = 10) {
    $conn = getDBConnection();
    $activities = [];
    
    // Get recent announcements
    $result = $conn->query("SELECT 'announcement' as type, title, created_at FROM announcements ORDER BY created_at DESC LIMIT $limit");
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    // Get recent opportunities
    $result = $conn->query("SELECT 'opportunity' as type, title, created_at FROM opportunities ORDER BY created_at DESC LIMIT $limit");
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    // Sort by date
    usort($activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return array_slice($activities, 0, $limit);
}

// Settings helpers (stored in config/settings.json)
function getAllSettings() {
    $file = __DIR__ . '/../config/settings.json';
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function getSetting($key, $default = null) {
    $settings = getAllSettings();
    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

function setSetting($key, $value) {
    $file = __DIR__ . '/../config/settings.json';
    $settings = getAllSettings();
    $settings[$key] = $value;
    // write safely
    $tmp = $file . '.tmp';
    file_put_contents($tmp, json_encode($settings, JSON_PRETTY_PRINT));
    rename($tmp, $file);
    return true;
}

// Calamity helpers
function ensureCalamitiesTable() {
    $conn = getDBConnection();
    $sql = "CREATE TABLE IF NOT EXISTS calamities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reporter_name VARCHAR(255) DEFAULT NULL,
        guardian_mother VARCHAR(255) DEFAULT NULL,
        guardian_father VARCHAR(255) DEFAULT NULL,
        guardian_other VARCHAR(255) DEFAULT NULL,
        nature TEXT,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        admin_response TEXT DEFAULT NULL,
        amount_given DECIMAL(10,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
}

function createCalamity($userId, $reporterName, $mother, $father, $otherGuardian, $nature) {
    ensureCalamitiesTable();
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO calamities (user_id, reporter_name, guardian_mother, guardian_father, guardian_other, nature) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssss', $userId, $reporterName, $mother, $father, $otherGuardian, $nature);
    $ok = $stmt->execute();
    if ($ok) {
        $insertId = $conn->insert_id;
        // notify executive admins
        notifyAdminsOfNewCalamity($insertId, $userId, $nature);
        return $insertId;
    }
    return false;
}

function notifyAdminsOfNewCalamity($calamityId, $userId, $nature) {
    $conn = getDBConnection();
    global $executiveRoles;
    if (empty($executiveRoles) || !is_array($executiveRoles)) {
        return;
    }
    // build role list safely
    $escaped = array_map(function($r) use ($conn) { return "'" . $conn->real_escape_string($r) . "'"; }, $executiveRoles);
    $roleList = implode(',', $escaped);
    $sql = "SELECT id, full_name FROM users WHERE role IN ($roleList)";
    $res = $conn->query($sql);
    $short = mb_strlen($nature) > 140 ? mb_substr($nature,0,137) . '...' : $nature;
    while ($admin = $res->fetch_assoc()) {
        $title = 'New Calamity Reported';
        $msg = sprintf("%s submitted a calamity: %s", ($admin['full_name'] ?? 'A user'), $short);
        // send in-app notification and email
        sendCalamityNotification($admin['id'], $title, $msg, '/admin/calamity-approvals.php');
    }
}

function getCalamitiesByUser($userId) {
    ensureCalamitiesTable();
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM calamities WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result();
}

function getAllCalamities() {
    ensureCalamitiesTable();
    $conn = getDBConnection();
    $result = $conn->query("SELECT c.*, u.full_name as submitter, u.email as submitter_email, c.user_id FROM calamities c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC");
    return $result;
}

// Send in-app notification and email for calamity updates
function sendCalamityNotification($userId, $title, $message, $link = null) {
    // in-app notification
    addNotification($userId, $title, $message, 'info', $link);

    // send email if user has email
    $user = getUserById($userId);
    if (!empty($user['email'])) {
        $subject = $title;
        $body = "Hello " . ($user['full_name'] ?? '') . ",\n\n" . $message . "\n\n" . "Regards,\nUYTSA Team";
        // Use mail() if configured on server
        @mail($user['email'], $subject, $body);
    }
}

// Notifications table helper and getters
function ensureNotificationsTable() {
    $conn = getDBConnection();
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        type VARCHAR(50) DEFAULT 'info',
        link VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
}

function getNotificationsForUser($userId, $limit = 5) {
    ensureNotificationsTable();
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, title, message, link, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY is_read ASC, created_at DESC LIMIT ?");
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

function countUnreadNotifications($userId) {
    ensureNotificationsTable();
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)($res['c'] ?? 0);
}

function markNotificationAsRead($id, $userId = null) {
    ensureNotificationsTable();
    $conn = getDBConnection();
    if ($userId) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $id, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
    }
    return $stmt->execute();
}

function getCalamityById($id) {
    ensureCalamitiesTable();
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM calamities WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateCalamity($id, $status, $adminResponse = null, $amount = 0) {
    ensureCalamitiesTable();
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE calamities SET status = ?, admin_response = ?, amount_given = ? WHERE id = ?");
    $stmt->bind_param('ssdi', $status, $adminResponse, $amount, $id);
    return $stmt->execute();
}
?>