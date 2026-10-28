<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'method'], 405);
if (!verify_csrf()) json_response(['error' => 'csrf'], 403);
$user = current_user();
if (!$user) json_response(['error' => 'login'], 401);
$input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
$videoId = (int)($input['video_id'] ?? 0);
$body = (string)($input['body'] ?? '');
if ($videoId < 1) json_response(['error' => 'invalid'], 400);
$comment = add_comment($videoId, (int)$user['id'], $body);
if (!$comment) json_response(['error' => 'empty'], 400);
$video = get_video($videoId);
json_response([
    'comment' => [
        'id' => (int)$comment['id'],
        'body' => $comment['body'],
        'display_name' => $comment['display_name'],
        'username' => $comment['username'],
        'avatar_url' => avatar_url($comment['avatar'], $comment['display_name']),
        'created_ago' => time_ago($comment['created_at']),
    ],
    'comments_count' => $video ? (int)$video['comments_count'] : 0,
]);
