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

// RBAC Permissions
define('PERM_FINANCES', 'finances');
define('PERM_EVENTS', 'events');
define('PERM_MEMBERS', 'members');
define('PERM_CONTENT', 'content'); // announcements, opportunities, gallery
define('PERM_SETTINGS', 'settings');

// Check if user has a specific permission
function hasPermission($permission) {
    if (!isLoggedIn()) return false;

    $role = $_SESSION['role'];
    
    // Admins have all permissions
    if (in_array($role, ['Patron', 'Chairperson', 'Vice_Chairperson'])) {
        return true;
    }

    switch ($permission) {
        case PERM_FINANCES:
            // Treasurer can manage finances
            return $role === 'Treasurer';
            
        case PERM_EVENTS:
        case PERM_MEMBERS:
        case PERM_CONTENT:
            // Secretary General, Organizing Secretary, Publicity Officer can manage content/events/members
            return in_array($role, ['Secretary_General', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket']);
            
        case PERM_SETTINGS:
            // Only top admins (handled above)
            return false;
            
        default:
            return false;
    }
}
?>