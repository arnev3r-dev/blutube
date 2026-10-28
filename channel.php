<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$uname = trim($_GET['u'] ?? '');
$stmt = db()->prepare('SELECT id, username, display_name, bio, avatar, role, created_at FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$uname]);
$channel = $stmt->fetch();
if (!$channel) {
    flash('error', 'Channel not found.');
    redirect('index.php');
}
$user = current_user();
$subscribed = $user ? is_subscribed((int)$user['id'], (int)$channel['id']) : false;
$videos = list_videos(['user_id' => (int)$channel['id'], 'limit' => 48]);
$pageTitle = $channel['display_name'];
require __DIR__ . '/includes/header.php';
?>
<meta name="csrf-token" content="<?= h(csrf_token()) ?>">
<div class="channel-row" style="border:none;padding:0 0 20px">
  <img src="<?= h(avatar_url($channel['avatar'], $channel['display_name'])) ?>" alt="" style="width:72px;height:72px">
  <div class="info">
    <strong style="font-size:1.25rem"><?= h($channel['display_name']) ?></strong>
    <span>@<?= h($channel['username']) ?> · Joined <?= time_ago($channel['created_at']) ?></span>
  </div>
  <?php if (!$user || (int)$user['id'] !== (int)$channel['id']): ?>
    <button type="button" class="btn-primary" data-subscribe="<?= (int)$channel['id'] ?>">
      <?= $subscribed ? 'Subscribed' : 'Subscribe' ?>
    </button>
  <?php endif; ?>
</div>
<div class="video-grid">
  <?php foreach ($videos as $v): ?>
    <a class="video-card" href="watch.php?id=<?= (int)$v['id'] ?>">
      <div class="thumb-wrap">
        <img src="<?= h($v['thumbnail']) ?>" alt="" loading="lazy">
        <span class="duration"><?= h($v['duration']) ?></span>
      </div>
      <div class="video-meta">
        <div>
          <h3><?= h($v['title']) ?></h3>
          <p><?= format_views((int)$v['views']) ?> views · <?= time_ago($v['created_at']) ?></p>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php if (!$videos): ?><p class="empty">No public videos on this channel.</p><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
