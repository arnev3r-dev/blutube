<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 10,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Blutube – DB error</title>';
        echo '<style>body{font-family:system-ui;background:#0b0d12;color:#f3f5f9;padding:40px;max-width:640px;margin:auto}';
        echo 'code{background:#1c2230;padding:2px 6px;border-radius:4px}pre{background:#141821;padding:16px;border-radius:8px;overflow:auto}</style></head><body>';
        echo '<h1>Database connection failed</h1>';
        echo '<p>Blutube could not connect to MySQL. Check these steps:</p>';
        echo '<ol>';
        echo '<li>In phpMyAdmin, select database <code>' . htmlspecialchars(DB_NAME) . '</code></li>';
        echo '<li>Run the full <code>install.sql</code> script once</li>';
        echo '<li>Confirm host/user/password in <code>config.php</code></li>';
        echo '<li>InfinityFree only allows MySQL from their own servers (site must be on InfinityFree, not localhost)</li>';
        echo '</ol>';
        echo '<p><strong>Error:</strong></p><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<p>Host: <code>' . htmlspecialchars(DB_HOST) . '</code> · DB: <code>' . htmlspecialchars(DB_NAME) . '</code> · User: <code>' . htmlspecialchars(DB_USER) . '</code></p>';
        exit;
    }

    return $pdo;
}
