<?php
require_once __DIR__ . '/config.php';

$username = $_SESSION['username'] ?? 'inconnu';
add_log('logout', 'Déconnexion de "' . $username . '"');
session_destroy();
echo json_encode(['success' => true]);
