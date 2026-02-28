<?php
$files = [
    'events.php',
    'announcements.php',
    'gallery.php',
    'calamity-response.php',
    'study-materials.php',
    'my-contributions.php',
    'profile.php',
    'notifications.php',
    'opportunities.php',
    'dashboard.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // 1. Remove <div class="container-fluid"> and <div class="row">
    $content = str_replace('<div class="container-fluid">', '', $content);
    $content = preg_replace('/<div class="row">\s*<!-- Sidebar -->/s', '<div class="dashboard-row">'."\n            ".'<!-- Sidebar -->', $content);
    
    // 2. We removed <div class="container-fluid">, meaning we need to remove one closing </div> near </body>
    $content = preg_replace('/<\/div>\s*(<\/div>\s*<\/body>)/s', '$1', $content);
    
    // 3. Convert <div class="col-md-9 col-lg-10 p-0 dashboard-main-content"> to <div class="dashboard-main-content">
    $content = preg_replace('/<div class="col-md-9 col-lg-10 p-0 dashboard-main-content">/s', '<div class="dashboard-main-content">', $content);
    
    file_put_contents($file, $content);
    echo "Fixed structure of $file\n";
}
?>
