<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$user = require_login();
$nav = 'studio';
$pageTitle = 'Studio';
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid session.';
    } elseif (isset($_POST['delete_id'])) {
        if (delete_video((int)$_POST['delete_id'], (int)$user['id'], $user['role'] === 'admin')) {
            $msg = 'Video deleted.';
        } else {
            $error = 'Could not delete video.';
        }
    } else {
        $result = create_video((int)$user['id'], [
            'title' => $_POST['title'] ?? '',
            'src' => $_POST['src'] ?? '',
            'thumbnail' => $_POST['thumbnail'] ?? '',
            'duration' => $_POST['duration'] ?? '',
            'category' => $_POST['category'] ?? 'General',
            'description' => $_POST['description'] ?? '',
        ]);
        if ($result['ok']) $msg = 'Video published!';
        else $error = $result['error'];
    }
}

$stmt = db()->prepare(
    'SELECT v.* FROM videos v WHERE v.user_id = ? ORDER BY v.created_at DESC LIMIT 100'
);
$stmt->execute([(int)$user['id']]);
$videos = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div>
    <h1>Studio</h1>
    <p class="muted">Add videos by public MP4 URL (no file upload to the server).</p>
  </div>
</div>
<?php if ($msg): ?><div class="flash flash-success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>
<div class="studio-grid">
  <div class="panel">
    <h2>Publish video</h2>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label>Title *</label><input name="title" required maxlength="255"></div>
      <div class="field"><label>Video URL (mp4) *</label><input name="src" required placeholder="https://.../video.mp4"></div>
      <div class="field"><label>Thumbnail URL</label><input name="thumbnail" placeholder="optional"></div>
      <div class="field" style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div><label>Duration</label><input name="duration" placeholder="4:20"></div>
        <div><label>Category</label><input name="category" value="General"></div>
      </div>
      <div class="field"><label>Description</label><textarea name="description" rows="3"></textarea></div>
      <button type="submit" class="btn-primary">Publish</button>
    </form>
  </div>
  <div class="panel">
    <h2>Your videos (<?= count($videos) ?>)</h2>
    <?php if (!$videos): ?>
      <p class="muted">None yet.</p>
    <?php else: ?>
      <?php foreach ($videos as $v): ?>
        <div class="list-row">
          <img src="<?= h($v['thumbnail']) ?>" alt="">
          <div class="info">
            <strong><?= h($v['title']) ?></strong>
            <span><?= format_views((int)$v['views']) ?> views · <?= time_ago($v['created_at']) ?></span>
          </div>
          <a class="btn-ghost" href="watch.php?id=<?= (int)$v['id'] ?>">View</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?>
            <input type="hidden" name="delete_id" value="<?= (int)$v['id'] ?>">
            <button type="submit" class="btn-danger">Delete</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
