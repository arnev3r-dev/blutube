<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$nav = 'subs';
$pageTitle = 'Subscriptions';
$ids = subscriber_channel_ids((int)$user['id']);
$videos = $ids ? list_videos(['channel_ids' => $ids, 'limit' => 40]) : [];
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Subscriptions</h1></div>
<?php if (!$ids): ?>
  <p class="empty">You are not subscribed to anyone yet.</p>
<?php elseif (!$videos): ?>
  <p class="empty">No videos from your subscriptions.</p>
<?php else: ?>
  <div class="video-grid">
    <?php foreach ($videos as $v): ?>
      <a class="video-card" href="watch.php?id=<?= (int)$v['id'] ?>">
        <div class="thumb-wrap">
          <img src="<?= h($v['thumbnail']) ?>" alt="" loading="lazy">
          <span class="duration"><?= h($v['duration']) ?></span>
        </div>
        <div class="video-meta">
          <img src="<?= h(avatar_url($v['avatar'], $v['display_name'])) ?>" alt="">
          <div>
            <h3><?= h($v['title']) ?></h3>
            <p><?= h($v['display_name']) ?></p>
            <p><?= format_views((int)$v['views']) ?> views · <?= time_ago($v['created_at']) ?></p>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
