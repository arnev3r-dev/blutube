<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$nav = 'history';
$pageTitle = 'History';
$stmt = db()->prepare(
    'SELECT v.*, u.username, u.display_name, u.avatar, h.watched_at
     FROM watch_history h
     JOIN videos v ON v.id = h.video_id
     JOIN users u ON u.id = v.user_id
     WHERE h.user_id = ? ORDER BY h.watched_at DESC LIMIT 40'
);
$stmt->execute([(int)$user['id']]);
$videos = $stmt->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Watch history</h1></div>
<?php if (!$videos): ?>
  <p class="empty">Nothing watched yet.</p>
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
            <p>Watched <?= time_ago($v['watched_at']) ?></p>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
