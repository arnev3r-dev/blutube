<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_KEY . '" value="' . h(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    start_session();
    $token = $token ?? ($_POST[CSRF_TOKEN_KEY] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token)
        && !empty($_SESSION[CSRF_TOKEN_KEY])
        && hash_equals($_SESSION[CSRF_TOKEN_KEY], $token);
}

function current_user(): ?array
{
    start_session();
    if (empty($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null && (int)$cache['id'] === (int)$_SESSION['user_id']) {
        return $cache;
    }
    $stmt = db()->prepare('SELECT id, username, email, display_name, bio, avatar, role, is_banned, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || (int)$user['is_banned'] === 1) {
        logout_user();
        return null;
    }
    $cache = $user;
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        flash('error', 'Admin access required.');
        redirect('index.php');
    }
    return $user;
}

function login_user(int $userId): void
{
    start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool)$p['secure'], (bool)$p['httponly']);
    }
    session_destroy();
}

function register_user(string $username, string $email, string $password, string $displayName = ''): array
{
    $username = trim($username);
    $email = strtolower(trim($email));
    $displayName = trim($displayName) !== '' ? trim($displayName) : $username;

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        return ['ok' => false, 'error' => 'Username must be 3–30 characters (letters, numbers, underscore).'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Invalid email address.'];
    }
    if (strlen($password) < 6) {
        return ['ok' => false, 'error' => 'Password must be at least 6 characters.'];
    }

    $hash = password_hash($password, PASSWORD_ALGO);
    try {
        $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, display_name) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $hash, $displayName]);
        return ['ok' => true, 'user_id' => (int)db()->lastInsertId()];
    } catch (PDOException $e) {
        if ((int)$e->getCode() === 23000) {
            return ['ok' => false, 'error' => 'Username or email already taken.'];
        }
        return ['ok' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

function attempt_login(string $login, string $password): array
{
    $login = trim($login);
    $stmt = db()->prepare('SELECT id, password_hash, is_banned FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$login, strtolower($login)]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return ['ok' => false, 'error' => 'Invalid username/email or password.'];
    }
    if ((int)$row['is_banned'] === 1) {
        return ['ok' => false, 'error' => 'This account is banned.'];
    }
    login_user((int)$row['id']);
    return ['ok' => true, 'user_id' => (int)$row['id']];
}
