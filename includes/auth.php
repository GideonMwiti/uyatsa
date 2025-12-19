<?php
// Authentication functions - Development version with plain passwords

// Check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Authenticate user (plain password version for development)
function authenticate($username, $password) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Development: Simple password comparison (plain text)
        // In production, use: password_verify($password, $user['password'])
        if ($password === $user['password']) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            return true;
        }
    }
    
    return false;
}
?>