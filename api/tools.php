<?php
require_once __DIR__ . '/config.php';

$tools = read_json(TOOLS_FILE);

$public = array_map(function($t) {
    return [
        'id' => $t['id'],
        'name' => $t['name'],
        'description' => $t['description'],
        'category' => $t['category'],
        'version' => $t['version'],
        'buttonColor' => $t['buttonColor'] ?? '',
        'size' => $t['size'],
        'createdAt' => $t['createdAt']
    ];
}, $tools);

echo json_encode(array_values($public));
