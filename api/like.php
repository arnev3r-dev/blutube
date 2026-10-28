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
if ($videoId < 1) json_response(['error' => 'invalid'], 400);
json_response(toggle_like((int)$user['id'], $videoId));
