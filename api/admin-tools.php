<?php
require_once __DIR__ . '/config.php';
require_auth();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (empty($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Aucun fichier fourni']);
        exit;
    }

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur upload: ' . $file['error']]);
        exit;
    }

    $uniqueName = bin2hex(random_bytes(16)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $dest = UPLOADS_DIR . '/' . $uniqueName;
    move_uploaded_file($file['tmp_name'], $dest);

    $tools = read_json(TOOLS_FILE);
    $newTool = [
        'id' => bin2hex(random_bytes(16)),
        'name' => $_POST['name'] ?: $file['name'],
        'description' => $_POST['description'] ?? '',
        'category' => $_POST['category'] ?? 'Utilitaires',
        'version' => $_POST['version'] ?? '1.0',
        'buttonColor' => $_POST['buttonColor'] ?? '',
        'filename' => $uniqueName,
        'originalName' => $file['name'],
        'size' => $file['size'],
        'createdAt' => date('c')
    ];

    $tools[] = $newTool;
    write_json(TOOLS_FILE, $tools);
    add_log('tool_add', 'Ajout de l\'outil "' . $newTool['name'] . '"');
    echo json_encode($newTool);
    exit;
}

if ($method === 'PUT') {
    $id = $_GET['id'] ?? '';
    $input = get_input();
    $tools = read_json(TOOLS_FILE);

    $idx = null;
    foreach ($tools as $i => $t) {
        if ($t['id'] === $id) { $idx = $i; break; }
    }

    if ($idx === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Outil introuvable']);
        exit;
    }

    $allowed = ['name', 'description', 'category', 'version', 'buttonColor'];
    foreach ($allowed as $key) {
        if (isset($input[$key])) $tools[$idx][$key] = $input[$key];
    }

    write_json(TOOLS_FILE, $tools);
    add_log('tool_edit', 'Modification de l\'outil "' . $tools[$idx]['name'] . '"');
    echo json_encode($tools[$idx]);
    exit;
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    $tools = read_json(TOOLS_FILE);

    $idx = null;
    foreach ($tools as $i => $t) {
        if ($t['id'] === $id) { $idx = $i; break; }
    }

    if ($idx === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Outil introuvable']);
        exit;
    }

    $tool = $tools[$idx];
    $filepath = UPLOADS_DIR . '/' . basename($tool['filename']);
    if (file_exists($filepath)) unlink($filepath);

    array_splice($tools, $idx, 1);
    write_json(TOOLS_FILE, $tools);
    add_log('tool_delete', 'Suppression de l\'outil "' . $tool['name'] . '"');
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Méthode non supportée']);
