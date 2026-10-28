<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$nav = 'home';
$pageTitle = 'Home';
$category = trim($_GET['category'] ?? 'All');

try {
    $videos = list_videos(['category' => $category]);
    $categories = get_categories();
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Error loading videos</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    echo '<p>Open <a href="check.php">check.php</a> for diagnostics. If tables are missing, run install.sql in phpMyAdmin.</p>';
    exit;
}

require __DIR__ . '/includes/header.php';
?>
<div class="chips">
  <?php foreach ($categories as $cat): ?>
    <a class="chip <?= $cat === $category ? 'active' : '' ?>"
       href="index.php?category=<?= urlencode($cat) ?>"><?= h($cat) ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$videos): ?>
  <p class="empty">No videos yet. <a href="studio.php">Upload one</a> or run install.sql for seed data.</p>
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
