<?php
require_once __DIR__ . '/config.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

$tools = read_json(TOOLS_FILE);
$tool = null;
foreach ($tools as $t) {
    if ($t['id'] === $id) { $tool = $t; break; }
}

if (!$tool) {
    http_response_code(404);
    echo json_encode(['error' => 'Outil introuvable']);
    exit;
}

$filepath = UPLOADS_DIR . '/' . basename($tool['filename']);
if (!file_exists($filepath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Fichier introuvable']);
    exit;
}

add_log('download', 'Téléchargement de "' . $tool['name'] . '"');

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $tool['originalName'] . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache');

readfile($filepath);
exit;
