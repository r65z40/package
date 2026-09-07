<?php
require_once __DIR__ . '/config.php';
require_auth();

$logs = read_json(LOGS_FILE);
$page = intval($_GET['page'] ?? 1);
$limit = intval($_GET['limit'] ?? 50);
$filter = $_GET['filter'] ?? '';

if ($filter) {
    $f = mb_strtolower($filter);
    $logs = array_values(array_filter($logs, function($l) use ($f) {
        return str_contains(mb_strtolower($l['action']), $f)
            || str_contains(mb_strtolower($l['details']), $f)
            || str_contains($l['ip'], $f);
    }));
}

$total = count($logs);
$start = ($page - 1) * $limit;
$paginated = array_slice($logs, $start, $limit);

echo json_encode([
    'logs' => $paginated,
    'total' => $total,
    'page' => $page,
    'totalPages' => max(1, ceil($total / $limit))
]);
