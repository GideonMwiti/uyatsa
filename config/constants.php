<?php
// Application Constants
define('APP_NAME', 'UYTSA Community System');
define('APP_VERSION', '1.0.0');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('PROFILE_IMAGE_PATH', 'assets/uploads/profile/');
define('GALLERY_IMAGE_PATH', 'assets/uploads/gallery/');
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20MB

// User roles
$roles = [
    'member' => 'Member',
    'Patron' => 'Patron',
    'Chairperson' => 'Chairperson',
    'Vice_Chairperson' => 'Vice Chairperson',
    'Secretary_General' => 'Secretary General',
    'Treasurer' => 'Treasurer',
    'Organizing_Secretary' => 'Organizing Secretary',
    'Publicity_Officer' => 'Publicity Officer',
    'NextGen_Docket' => 'NextGen Docket'
];

// Opportunity types
$opportunityTypes = [
    'internship' => 'Internship',
    'scholarship' => 'Scholarship',
    'job' => 'Job',
    'volunteer' => 'Volunteer',
    'training' => 'Training',
    'competition' => 'Competition'
];

// Executive roles (admin panel)
$executiveRoles = [
    'Patron', 'Chairperson', 'Vice_Chairperson', 'Secretary_General',
    'Treasurer', 'Organizing_Secretary', 'Publicity_Officer', 'NextGen_Docket'
];
?>