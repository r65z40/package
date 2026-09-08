<?php
require_once __DIR__ . '/config.php';

$tools = read_json(TOOLS_FILE);

$knownFiles = [];
foreach ($tools as $t) {
    $knownFiles[$t['filename']] = true;
}

$changed = false;
$uploadsDir = UPLOADS_DIR;
if (is_dir($uploadsDir)) {
    $files = scandir($uploadsDir);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..' || $f === '.gitkeep' || $f === '.htaccess') continue;
        if (isset($knownFiles[$f])) continue;

        $filepath = $uploadsDir . '/' . $f;
        if (!is_file($filepath)) continue;

        $tools[] = [
            'id' => bin2hex(random_bytes(16)),
            'name' => preg_replace('/\.[^.]+$/', '', $f),
            'description' => '',
            'category' => 'Utilitaires',
            'version' => '',
            'buttonColor' => '',
            'filename' => $f,
            'originalName' => $f,
            'size' => filesize($filepath),
            'createdAt' => date('c', filemtime($filepath))
        ];
        $changed = true;
    }
}

if ($changed) {
    write_json(TOOLS_FILE, $tools);
}

$public = array_map(function($t) {
    return [
        'id' => $t['id'],
        'name' => $t['name'],
        'description' => $t['description'] ?? '',
        'category' => $t['category'] ?? 'Utilitaires',
        'version' => $t['version'] ?? '',
        'buttonColor' => $t['buttonColor'] ?? '',
        'size' => $t['size'] ?? 0,
        'createdAt' => $t['createdAt'] ?? ''
    ];
}, $tools);

echo json_encode(array_values($public));
