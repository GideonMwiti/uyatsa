<?php
// verify_login_fix.php
require_once 'config/database.php';

function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

function simulateLogin($conn, $username, $password) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 1) return "User not found";
    $user = $result->fetch_assoc();
    if (!(password_verify($password, $user['password']) || $password === $user['password'])) return "Invalid password";
    $isApproved = ($user['is_approved'] ?? 1);
    if ($user['role'] === 'member' && $isApproved == 0) return "Pending approval";
    return "Login success for role: {$user['role']}";
}

$conn = getDBConnection();
$checks = [];
$checks[] = 'is_approved column: ' . (columnExists($conn, 'users', 'is_approved') ? 'exists' : 'missing');

// Simulate admin login
$checks[] = 'Admin login: ' . simulateLogin($conn, 'admin', 'admin123');
// Simulate member login (pending)
$memberStmt = $conn->query("SELECT username, password FROM users WHERE role='member' AND is_approved=0 LIMIT 1");
if ($memberStmt && $memberStmt->num_rows > 0) {
    $row = $memberStmt->fetch_assoc();
    $checks[] = 'Member login (pending): ' . simulateLogin($conn, $row['username'], $row['password']);
} else {
    $checks[] = 'No pending member found for test.';
}
// Simulate member login (approved)
$memberStmt2 = $conn->query("SELECT username, password FROM users WHERE role='member' AND is_approved=1 LIMIT 1");
if ($memberStmt2 && $memberStmt2->num_rows > 0) {
    $row = $memberStmt2->fetch_assoc();
    $checks[] = 'Member login (approved): ' . simulateLogin($conn, $row['username'], $row['password']);
} else {
    $checks[] = 'No approved member found for test.';
}

foreach ($checks as $check) {
    echo $check . "<br>\n";
}
