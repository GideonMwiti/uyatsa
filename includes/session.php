<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role) {
    if (!isLoggedIn()) return false;
    
    if (is_array($role)) {
        return in_array($_SESSION['role'], $role);
    }
    
    return $_SESSION['role'] === $role;
}

// Check if user is executive
function isExecutive() {
    if (!isLoggedIn()) return false;
    
    $executiveRoles = ['Patron', 'Chairperson', 'Vice_Chairperson', 'Secretary_General',
                      'Treasurer', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket'];
    
    return in_array($_SESSION['role'], $executiveRoles);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

// Redirect if not executive
function requireExecutive() {
    requireLogin();
    if (!isExecutive()) {
        header('Location: ../user/dashboard.php');
        exit();
    }
}
?>