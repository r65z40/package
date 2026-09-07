<?php
/**
 * Script d'initialisation du compte admin.
 * Accédez à setup.php depuis votre navigateur, puis SUPPRIMEZ ce fichier.
 */

$usersFile = __DIR__ . '/data/users.json';
$message = '';
$done = false;

if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);

$existing = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
if (!empty($existing)) {
    $done = true;
    $message = 'Un compte admin existe déjà. Supprimez data/users.json pour en recréer un.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$done) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 3 || strlen($password) < 6) {
        $message = 'Le nom doit faire 3+ caractères et le mot de passe 6+ caractères.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        file_put_contents($usersFile, json_encode([
            ['username' => $username, 'password' => $hash]
        ], JSON_PRETTY_PRINT));

        if (!file_exists(__DIR__ . '/data/tools.json'))
            file_put_contents(__DIR__ . '/data/tools.json', '[]');
        if (!file_exists(__DIR__ . '/data/logs.json'))
            file_put_contents(__DIR__ . '/data/logs.json', '[]');

        $done = true;
        $message = 'Compte admin créé ! Supprimez maintenant setup.php de votre serveur.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Installation - Cedelia Tools Portal</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f5f7fb; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .card { background: #fff; border-radius: 12px; padding: 2.5rem; max-width: 400px; width: 100%; box-shadow: 0 2px 20px rgba(0,0,0,0.08); }
    h1 { color: #1038e0; font-size: 1.4rem; text-align: center; margin-bottom: 0.5rem; }
    .sub { text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 1.5rem; }
    label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 6px; margin-top: 1rem; }
    input { width: 100%; padding: 10px 14px; border: 1px solid #e2e6f0; border-radius: 8px; font-size: 0.9rem; }
    input:focus { outline: none; border-color: #1038e0; }
    button { width: 100%; padding: 12px; background: #1038e0; color: #fff; border: none; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; margin-top: 1.5rem; }
    button:hover { background: #0b2aad; }
    .msg { text-align: center; padding: 12px; border-radius: 8px; margin-top: 1rem; font-size: 0.875rem; }
    .msg.ok { background: #d1fae5; color: #059669; }
    .msg.err { background: #fee2e2; color: #dc2626; }
    .warn { text-align: center; margin-top: 1rem; padding: 10px; background: #fef3c7; color: #92400e; border-radius: 8px; font-size: 0.8rem; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Installation</h1>
    <p class="sub">Cedelia Tools Portal</p>

    <?php if ($message): ?>
      <div class="msg <?= $done ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$done): ?>
    <form method="POST">
      <label for="username">Nom d'utilisateur</label>
      <input type="text" id="username" name="username" required minlength="3" value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>">
      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required minlength="6">
      <button type="submit">Créer le compte admin</button>
    </form>
    <?php else: ?>
      <div class="warn">Pensez à supprimer ce fichier (setup.php) de votre serveur pour des raisons de sécurité.</div>
      <a href="admin.html" style="display:block;text-align:center;margin-top:1rem;color:#1038e0">Aller à l'administration →</a>
    <?php endif; ?>
  </div>
</body>
</html>
