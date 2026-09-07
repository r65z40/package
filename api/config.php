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

define('DATA_DIR', __DIR__ . '/../data');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('TOOLS_FILE', DATA_DIR . '/tools.json');
define('USERS_FILE', DATA_DIR . '/users.json');
define('LOGS_FILE', DATA_DIR . '/logs.json');
define('SESSIONS_FILE', DATA_DIR . '/sessions.json');
define('TOKEN_LIFETIME', 3600);

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

function create_auth_token($username) {
    $token = bin2hex(random_bytes(32));
    $sessions = read_json(SESSIONS_FILE);
    $sessions = array_filter($sessions, function($s) {
        return $s['expires'] > time();
    });
    $sessions[$token] = [
        'username' => $username,
        'expires' => time() + TOKEN_LIFETIME
    ];
    write_json(SESSIONS_FILE, $sessions);
    setcookie('cedelia_token', $token, time() + TOKEN_LIFETIME, '/', '', false, true);
    return $token;
}

function get_auth_token() {
    if (!empty($_COOKIE['cedelia_token'])) return $_COOKIE['cedelia_token'];
    if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) return $_SERVER['HTTP_X_AUTH_TOKEN'];
    return '';
}

function check_auth() {
    $token = get_auth_token();
    if (!$token) return false;
    $sessions = read_json(SESSIONS_FILE);
    if (!isset($sessions[$token])) return false;
    if ($sessions[$token]['expires'] < time()) {
        unset($sessions[$token]);
        write_json(SESSIONS_FILE, $sessions);
        return false;
    }
    return true;
}

function require_auth() {
    if (!check_auth()) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorisé']);
        exit;
    }
}

function destroy_auth_token() {
    $token = get_auth_token();
    if ($token) {
        $sessions = read_json(SESSIONS_FILE);
        unset($sessions[$token]);
        write_json(SESSIONS_FILE, $sessions);
    }
    setcookie('cedelia_token', '', time() - 3600, '/', '', false, true);
}

function get_input() {
    return json_decode(file_get_contents('php://input'), true) ?: [];
}
