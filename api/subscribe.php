<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error' => 'method'], 405);
if (!verify_csrf()) json_response(['error' => 'csrf'], 403);
$user = current_user();
if (!$user) json_response(['error' => 'login'], 401);
$input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
$channelId = (int)($input['channel_id'] ?? 0);
if ($channelId < 1) json_response(['error' => 'invalid'], 400);
$result = toggle_subscribe((int)$user['id'], $channelId);
if (isset($result['error'])) json_response($result, 400);
json_response($result);
