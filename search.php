<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$q = trim($_GET['q'] ?? '');
$pageTitle = $q !== '' ? 'Search: ' . $q : 'Search';
$videos = $q !== '' ? list_videos(['q' => $q]) : [];
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1><?= $q !== '' ? 'Results for “' . h($q) . '”' : 'Search' ?></h1></div>
<?php if ($q === ''): ?>
  <p class="empty">Type something in the search box.</p>
<?php elseif (!$videos): ?>
  <p class="empty">No videos found.</p>
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
