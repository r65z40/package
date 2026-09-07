<?php
require_once __DIR__ . '/config.php';

$token = get_auth_token();
$authenticated = check_auth();

$debug = [];
$debug['token_found'] = $token ? true : false;
$debug['token_source'] = '';
if (!empty($_GET['token'])) $debug['token_source'] = 'GET';
elseif (!empty($_POST['token'])) $debug['token_source'] = 'POST';
elseif (!empty($_COOKIE['cedelia_token'])) $debug['token_source'] = 'COOKIE';
elseif (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) $debug['token_source'] = 'HEADER';
$debug['sessions_file_exists'] = file_exists(SESSIONS_FILE);
$debug['sessions_file_path'] = SESSIONS_FILE;

if (file_exists(SESSIONS_FILE)) {
    $sessions = read_json(SESSIONS_FILE);
    $debug['sessions_count'] = count($sessions);
    if ($token) {
        $debug['token_in_sessions'] = isset($sessions[$token]);
    }
}

echo json_encode(['authenticated' => $authenticated, 'debug' => $debug]);
