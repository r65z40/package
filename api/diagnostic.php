<?php
header('Content-Type: text/html; charset=utf-8');
echo '<h2>Diagnostic Cedelia</h2><pre>';

$dataDir = __DIR__ . '/../data';
$uploadsDir = __DIR__ . '/../uploads';
$sessionsFile = $dataDir . '/sessions.json';
$usersFile = $dataDir . '/users.json';
$toolsFile = $dataDir . '/tools.json';
$logsFile = $dataDir . '/logs.json';

echo "PHP version: " . phpversion() . "\n\n";

echo "=== CHEMINS ===\n";
echo "data/ : " . realpath($dataDir) . "\n";
echo "uploads/ : " . realpath($uploadsDir) . "\n\n";

echo "=== REPERTOIRES ===\n";
echo "data/ existe : " . (is_dir($dataDir) ? 'OUI' : 'NON') . "\n";
echo "data/ accessible en ecriture : " . (is_writable($dataDir) ? 'OUI' : 'NON') . "\n";
echo "uploads/ existe : " . (is_dir($uploadsDir) ? 'OUI' : 'NON') . "\n";
echo "uploads/ accessible en ecriture : " . (is_writable($uploadsDir) ? 'OUI' : 'NON') . "\n\n";

echo "=== FICHIERS ===\n";
$files = [$usersFile, $toolsFile, $logsFile, $sessionsFile];
foreach ($files as $f) {
    $name = basename($f);
    $exists = file_exists($f);
    echo "$name : " . ($exists ? 'existe' : 'MANQUANT');
    if ($exists) {
        echo " | lisible: " . (is_readable($f) ? 'oui' : 'NON');
        echo " | modifiable: " . (is_writable($f) ? 'oui' : 'NON');
        echo " | taille: " . filesize($f) . " octets";
    }
    echo "\n";
}

echo "\n=== TEST ECRITURE ===\n";
$testFile = $dataDir . '/_test_write.tmp';
$writeOk = @file_put_contents($testFile, 'test');
if ($writeOk !== false) {
    echo "Ecriture dans data/ : OK\n";
    @unlink($testFile);
} else {
    echo "Ecriture dans data/ : ECHEC — chmod 755 ou 777 necessaire sur le dossier data/\n";
}

if (!file_exists($sessionsFile)) {
    echo "\n=== CREATION sessions.json ===\n";
    $r = @file_put_contents($sessionsFile, '{}');
    echo ($r !== false) ? "sessions.json cree avec succes\n" : "ECHEC creation sessions.json\n";
}

echo "\n=== CONTENU sessions.json ===\n";
if (file_exists($sessionsFile)) {
    $content = file_get_contents($sessionsFile);
    echo $content . "\n";
} else {
    echo "(fichier manquant)\n";
}

echo "\n=== UTILISATEURS ===\n";
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true);
    if ($users && count($users) > 0) {
        echo count($users) . " utilisateur(s) configure(s)\n";
        foreach ($users as $u) {
            echo "- " . ($u['username'] ?? '?') . "\n";
        }
    } else {
        echo "AUCUN utilisateur ! Visitez setup.php pour creer un compte admin.\n";
    }
} else {
    echo "users.json MANQUANT ! Visitez setup.php pour creer un compte admin.\n";
}

echo '</pre>';
