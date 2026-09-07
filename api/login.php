<?php
require_once __DIR__ . '/config.php';

$input = get_input();
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

$users = read_json(USERS_FILE);
$found = null;
foreach ($users as $u) {
    if ($u['username'] === $username) { $found = $u; break; }
}

if (!$found || !password_verify($password, $found['password'])) {
    add_log('login_failed', 'Tentative de connexion échouée pour "' . ($username ?: '(vide)') . '"');
    http_response_code(401);
    echo json_encode(['error' => 'Identifiants incorrects']);
    exit;
}

$_SESSION['authenticated'] = true;
$_SESSION['username'] = $found['username'];
add_log('login', 'Connexion réussie de "' . $found['username'] . '"');
echo json_encode(['success' => true]);
