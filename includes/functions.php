<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function h(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    if (!preg_match('#^https?://#i', $path) && APP_URL !== '') {
        $path = rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
    }
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    start_session();
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    start_session();
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function format_views(int $n): string
{
    if ($n >= 1_000_000_000) return round($n / 1_000_000_000, 1) . 'B';
    if ($n >= 1_000_000) return round($n / 1_000_000, 1) . 'M';
    if ($n >= 1_000) return round($n / 1_000, 1) . 'K';
    return (string)$n;
}

function time_ago(string $datetime): string
{
    $ts = strtotime($datetime);
    if ($ts === false) return $datetime;
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return (int)floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return (int)floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return (int)floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return (int)floor($diff / 604800) . ' weeks ago';
    if ($diff < 31536000) return (int)floor($diff / 2592000) . ' months ago';
    return (int)floor($diff / 31536000) . ' years ago';
}

function avatar_url(?string $avatar, string $name): string
{
    if ($avatar) return $avatar;
    return DEFAULT_AVATAR . urlencode($name);
}

function list_videos(array $opts = []): array
{
    $where = ['v.is_public = 1'];
    $params = [];

    if (!empty($opts['category']) && $opts['category'] !== 'All') {
        $where[] = 'v.category = ?';
        $params[] = $opts['category'];
    }
    if (!empty($opts['q'])) {
        $where[] = '(v.title LIKE ? OR v.description LIKE ?)';
        $q = '%' . $opts['q'] . '%';
        $params[] = $q;
        $params[] = $q;
    }
    if (!empty($opts['user_id'])) {
        $where[] = 'v.user_id = ?';
        $params[] = (int)$opts['user_id'];
    }
    if (!empty($opts['channel_ids']) && is_array($opts['channel_ids'])) {
        $ids = array_map('intval', $opts['channel_ids']);
        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $where[] = "v.user_id IN ($placeholders)";
            $params = array_merge($params, $ids);
        }
    }

    $order = 'v.created_at DESC';
    if (($opts['sort'] ?? '') === 'views') $order = 'v.views DESC';
    elseif (($opts['sort'] ?? '') === 'likes') $order = 'v.likes_count DESC';

    $limit = (int)($opts['limit'] ?? ITEMS_PER_PAGE);
    $offset = (int)($opts['offset'] ?? 0);

    $sql = 'SELECT v.*, u.username, u.display_name, u.avatar
            FROM videos v JOIN users u ON u.id = v.user_id
            WHERE ' . implode(' AND ', $where) . "
            ORDER BY $order LIMIT $limit OFFSET $offset";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_video(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT v.*, u.username, u.display_name, u.avatar
         FROM videos v JOIN users u ON u.id = v.user_id WHERE v.id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_categories(): array
{
    try {
        $stmt = db()->query(
            "SELECT category, COUNT(*) AS cnt FROM videos WHERE is_public = 1 GROUP BY category ORDER BY cnt DESC, category ASC"
        );
        $rows = $stmt->fetchAll();
    } catch (Throwable $e) {
        return ['All'];
    }
    $cats = ['All'];
    foreach ($rows as $r) $cats[] = $r['category'];
    return $cats;
}

function increment_views(int $videoId, ?int $userId = null): void
{
    db()->prepare('UPDATE videos SET views = views + 1 WHERE id = ?')->execute([$videoId]);
    if ($userId) {
        db()->prepare(
            'INSERT INTO watch_history (user_id, video_id, watched_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE watched_at = NOW()'
        )->execute([$userId, $videoId]);
    }
}

function toggle_like(int $userId, int $videoId): array
{
    $pdo = db();
    $check = $pdo->prepare('SELECT 1 FROM likes WHERE user_id = ? AND video_id = ?');
    $check->execute([$userId, $videoId]);
    if ($check->fetch()) {
        $pdo->prepare('DELETE FROM likes WHERE user_id = ? AND video_id = ?')->execute([$userId, $videoId]);
        $pdo->prepare('UPDATE videos SET likes_count = GREATEST(0, likes_count - 1) WHERE id = ?')->execute([$videoId]);
        $liked = false;
    } else {
        $pdo->prepare('INSERT INTO likes (user_id, video_id) VALUES (?, ?)')->execute([$userId, $videoId]);
        $pdo->prepare('UPDATE videos SET likes_count = likes_count + 1 WHERE id = ?')->execute([$videoId]);
        $liked = true;
    }
    $cnt = $pdo->prepare('SELECT likes_count FROM videos WHERE id = ?');
    $cnt->execute([$videoId]);
    return ['liked' => $liked, 'likes' => (int)$cnt->fetchColumn()];
}

function user_liked(int $userId, int $videoId): bool
{
    $stmt = db()->prepare('SELECT 1 FROM likes WHERE user_id = ? AND video_id = ?');
    $stmt->execute([$userId, $videoId]);
    return (bool)$stmt->fetch();
}

function toggle_subscribe(int $subscriberId, int $channelId): array
{
    if ($subscriberId === $channelId) {
        return ['subscribed' => false, 'error' => 'Cannot subscribe to yourself'];
    }
    $pdo = db();
    $check = $pdo->prepare('SELECT 1 FROM subscriptions WHERE subscriber_id = ? AND channel_id = ?');
    $check->execute([$subscriberId, $channelId]);
    if ($check->fetch()) {
        $pdo->prepare('DELETE FROM subscriptions WHERE subscriber_id = ? AND channel_id = ?')->execute([$subscriberId, $channelId]);
        $sub = false;
    } else {
        $pdo->prepare('INSERT INTO subscriptions (subscriber_id, channel_id) VALUES (?, ?)')->execute([$subscriberId, $channelId]);
        $sub = true;
    }
    return ['subscribed' => $sub];
}

function is_subscribed(int $subscriberId, int $channelId): bool
{
    $stmt = db()->prepare('SELECT 1 FROM subscriptions WHERE subscriber_id = ? AND channel_id = ?');
    $stmt->execute([$subscriberId, $channelId]);
    return (bool)$stmt->fetch();
}

function add_comment(int $videoId, int $userId, string $body): ?array
{
    $body = trim($body);
    if ($body === '' || mb_strlen($body) > 2000) return null;
    $pdo = db();
    $pdo->prepare('INSERT INTO comments (video_id, user_id, body) VALUES (?, ?, ?)')->execute([$videoId, $userId, $body]);
    $id = (int)$pdo->lastInsertId();
    $pdo->prepare('UPDATE videos SET comments_count = comments_count + 1 WHERE id = ?')->execute([$videoId]);
    $stmt = $pdo->prepare(
        'SELECT c.*, u.username, u.display_name, u.avatar FROM comments c
         JOIN users u ON u.id = c.user_id WHERE c.id = ?'
    );
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function list_comments(int $videoId, int $limit = 50): array
{
    $stmt = db()->prepare(
        'SELECT c.*, u.username, u.display_name, u.avatar
         FROM comments c JOIN users u ON u.id = c.user_id
         WHERE c.video_id = ? ORDER BY c.created_at DESC LIMIT ' . (int)$limit
    );
    $stmt->execute([$videoId]);
    return $stmt->fetchAll();
}

function create_video(int $userId, array $data): array
{
    $title = trim($data['title'] ?? '');
    $src = trim($data['src'] ?? '');
    if ($title === '' || $src === '') {
        return ['ok' => false, 'error' => 'Title and video URL are required.'];
    }
    if (!preg_match('#^https?://#i', $src)) {
        return ['ok' => false, 'error' => 'Video URL must start with http:// or https://'];
    }
    $thumb = trim($data['thumbnail'] ?? '');
    if ($thumb === '') {
        $thumb = 'https://picsum.photos/seed/' . md5($title . $src) . '/640/360';
    }
    $stmt = db()->prepare(
        'INSERT INTO videos (user_id, title, description, src, thumbnail, duration, category)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $title,
        trim($data['description'] ?? ''),
        $src,
        $thumb,
        trim($data['duration'] ?? '0:00') ?: '0:00',
        trim($data['category'] ?? 'General') ?: 'General',
    ]);
    return ['ok' => true, 'id' => (int)db()->lastInsertId()];
}

function delete_video(int $videoId, ?int $userId = null, bool $admin = false): bool
{
    if ($admin) {
        $stmt = db()->prepare('DELETE FROM videos WHERE id = ?');
        $stmt->execute([$videoId]);
        return $stmt->rowCount() > 0;
    }
    $stmt = db()->prepare('DELETE FROM videos WHERE id = ? AND user_id = ?');
    $stmt->execute([$videoId, $userId]);
    return $stmt->rowCount() > 0;
}

function subscriber_channel_ids(int $userId): array
{
    $stmt = db()->prepare('SELECT channel_id FROM subscriptions WHERE subscriber_id = ?');
    $stmt->execute([$userId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
