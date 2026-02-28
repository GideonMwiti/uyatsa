<?php
require_once 'config/database.php';
// Get DB connection
$conn = getDBConnection();
if (!$conn) {
    die('Database connection not found.');
}

// Check if is_approved column exists
$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_approved'");
if ($colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN is_approved TINYINT(1) DEFAULT 0");
    echo "Added is_approved column.<br>";
} else {
    echo "is_approved column already exists.<br>";
}
// Set all users to approved
$conn->query("UPDATE users SET is_approved = 1");
echo "All users set to approved.<br>";
?>
