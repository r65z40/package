<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
    exit;
});

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

session_start();

define('DATA_DIR', __DIR__ . '/../data');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('TOOLS_FILE', DATA_DIR . '/tools.json');
define('USERS_FILE', DATA_DIR . '/users.json');
define('LOGS_FILE', DATA_DIR . '/logs.json');
define('MAX_UPLOAD_SIZE', 500 * 1024 * 1024);

header('Content-Type: application/json; charset=utf-8');

function read_json($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function write_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function add_log($action, $details) {
    $logs = read_json(LOGS_FILE);
    array_unshift($logs, [
        'id' => bin2hex(random_bytes(16)),
        'timestamp' => date('c'),
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    if (count($logs) > 5000) $logs = array_slice($logs, 0, 5000);
    write_json(LOGS_FILE, $logs);
}

function require_auth() {
    if (empty($_SESSION['authenticated'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorisé']);
        exit;
    }
}

function get_input() {
    return json_decode(file_get_contents('php://input'), true) ?: [];
}
