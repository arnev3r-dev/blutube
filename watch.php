<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$video = $id ? get_video($id) : null;
if (!$video) {
    flash('error', 'Video not found.');
    redirect('index.php');
}

$user = current_user();
increment_views($id, $user ? (int)$user['id'] : null);
$liked = $user ? user_liked((int)$user['id'], $id) : false;
$subscribed = $user ? is_subscribed((int)$user['id'], (int)$video['user_id']) : false;
$comments = list_comments($id);
$related = list_videos(['limit' => 12, 'sort' => 'views']);
$related = array_values(array_filter($related, fn($r) => (int)$r['id'] !== $id));

$pageTitle = $video['title'];
require __DIR__ . '/includes/header.php';
?>
<meta name="csrf-token" content="<?= h(csrf_token()) ?>">
<div class="watch-layout">
  <div>
    <div class="player-wrap">
      <video controls autoplay playsinline src="<?= h($video['src']) ?>" poster="<?= h($video['thumbnail']) ?>"></video>
    </div>
    <h1 class="watch-title"><?= h($video['title']) ?></h1>
    <div class="watch-stats"><?= format_views((int)$video['views']) ?> views · <?= time_ago($video['created_at']) ?></div>
    <div class="watch-actions">
      <button type="button" class="btn-ghost <?= $liked ? 'active' : '' ?>" data-like="<?= (int)$video['id'] ?>">
        👍 <span data-count><?= (int)$video['likes_count'] ?></span>
      </button>
    </div>
    <div class="channel-row">
      <a href="channel.php?u=<?= urlencode($video['username']) ?>">
        <img src="<?= h(avatar_url($video['avatar'], $video['display_name'])) ?>" alt="">
      </a>
      <div class="info">
        <strong><a href="channel.php?u=<?= urlencode($video['username']) ?>"><?= h($video['display_name']) ?></a></strong>
        <span>@<?= h($video['username']) ?></span>
      </div>
      <?php if (!$user || (int)$user['id'] !== (int)$video['user_id']): ?>
        <button type="button" class="btn-primary" data-subscribe="<?= (int)$video['user_id'] ?>">
          <?= $subscribed ? 'Subscribed' : 'Subscribe' ?>
        </button>
      <?php endif; ?>
    </div>
    <?php if (trim((string)$video['description']) !== ''): ?>
      <div class="desc-box"><?= h($video['description']) ?></div>
    <?php endif; ?>
    <section class="comments">
      <h2><span data-comments-count><?= (int)$video['comments_count'] ?></span> Comments</h2>
      <?php if ($user): ?>
        <form id="comment-form" class="comment-form" data-video="<?= (int)$video['id'] ?>">
          <?= csrf_field() ?>
          <textarea name="body" rows="2" placeholder="Add a comment…" required maxlength="2000"></textarea>
          <button type="submit" class="btn-primary">Post</button>
        </form>
      <?php else: ?>
        <p class="muted"><a href="login.php">Log in</a> to comment.</p>
      <?php endif; ?>
      <div id="comments-list">
        <?php foreach ($comments as $c): ?>
          <div class="comment">
            <img src="<?= h(avatar_url($c['avatar'], $c['display_name'])) ?>" alt="">
            <div>
              <div class="meta"><strong><?= h($c['display_name']) ?></strong> · <?= time_ago($c['created_at']) ?></div>
              <div class="body"><?= h($c['body']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
  <aside class="related">
    <h2>Related</h2>
    <?php foreach ($related as $r): ?>
      <a class="related-item" href="watch.php?id=<?= (int)$r['id'] ?>">
        <div class="rt"><img src="<?= h($r['thumbnail']) ?>" alt="" loading="lazy"></div>
        <div class="ri">
          <h4><?= h($r['title']) ?></h4>
          <p><?= h($r['display_name']) ?></p>
          <p><?= format_views((int)$r['views']) ?> views</p>
        </div>
      </a>
    <?php endforeach; ?>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
