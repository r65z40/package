<?php
require_once __DIR__ . '/config.php';
echo json_encode(['authenticated' => check_auth()]);
