<?php
require_once __DIR__ . '/config.php';
$token = get_auth_token();
$sessions = read_json(SESSIONS_FILE);
$username = isset($sessions[$token]) ? $sessions[$token]['username'] : 'inconnu';
add_log('logout', 'Déconnexion de "' . $username . '"');
destroy_auth_token();
echo json_encode(['success' => true]);
