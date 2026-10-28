<?php
/**
 * Blutube diagnostic page – open this first if the site is blank.
 * Delete this file after everything works.
 */
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Blutube check</title>';
echo '<style>body{font-family:system-ui;background:#0b0d12;color:#f3f5f9;padding:32px;max-width:720px;margin:auto}';
echo '.ok{color:#4ade80}.bad{color:#f87171}code{background:#1c2230;padding:2px 6px;border-radius:4px}</style></head><body>';
echo '<h1>Blutube status check</h1><ul>';

$ok = true;

echo '<li class="ok">PHP version: ' . PHP_VERSION . '</li>';

$need = ['pdo_mysql', 'session', 'json', 'mbstring'];
foreach ($need as $ext) {
    if (extension_loaded($ext) || ($ext === 'mbstring' && function_exists('mb_strlen'))) {
        echo '<li class="ok">Extension: ' . $ext . '</li>';
    } else {
        // mbstring optional fallback
        if ($ext === 'mbstring') {
            echo '<li class="ok">Extension: mbstring (optional, using strlen fallback)</li>';
        } else {
            echo '<li class="bad">Missing extension: ' . $ext . '</li>';
            $ok = false;
        }
    }
}

$files = [
    'config.php', 'index.php', 'watch.php', 'login.php', 'register.php',
    'studio.php', 'includes/db.php', 'includes/auth.php', 'includes/functions.php',
    'includes/header.php', 'includes/footer.php', 'assets/style.css', 'assets/app.js',
];
foreach ($files as $f) {
    if (is_file(__DIR__ . '/' . $f)) {
        echo '<li class="ok">File: ' . htmlspecialchars($f) . '</li>';
    } else {
        echo '<li class="bad">Missing file: ' . htmlspecialchars($f) . '</li>';
        $ok = false;
    }
}

require_once __DIR__ . '/config.php';
echo '<li>DB host: <code>' . htmlspecialchars(DB_HOST) . '</code></li>';
echo '<li>DB name: <code>' . htmlspecialchars(DB_NAME) . '</code></li>';
echo '<li>DB user: <code>' . htmlspecialchars(DB_USER) . '</code></li>';

try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    echo '<li class="ok">MySQL connection: OK</li>';

    $tables = ['users', 'videos', 'comments', 'likes', 'subscriptions', 'watch_history'];
    foreach ($tables as $t) {
        try {
            $c = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo '<li class="ok">Table <code>' . $t . '</code>: ' . $c . ' rows</li>';
        } catch (Throwable $e) {
            echo '<li class="bad">Table <code>' . $t . '</code> missing – run <code>install.sql</code> in phpMyAdmin</li>';
            $ok = false;
        }
    }
} catch (Throwable $e) {
    echo '<li class="bad">MySQL connection failed: ' . htmlspecialchars($e->getMessage()) . '</li>';
    $ok = false;
}

echo '</ul>';
if ($ok) {
    echo '<p class="ok"><strong>All checks passed.</strong> Open <a href="index.php" style="color:#5b8cff">index.php</a></p>';
} else {
    echo '<p class="bad"><strong>Fix the items marked red, then reload this page.</strong></p>';
}
echo '<p style="color:#9aa3b5;font-size:0.9rem">Delete <code>check.php</code> when done.</p></body></html>';
