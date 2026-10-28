<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$nav = 'admin';
$pageTitle = 'Videos · Admin';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $vid = (int)($_POST['video_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($vid > 0 && $action === 'delete') {
        if (delete_video($vid, null, true)) $msg = 'Deleted.';
    } elseif ($vid > 0 && $action === 'hide') {
        db()->prepare('UPDATE videos SET is_public = 0 WHERE id = ?')->execute([$vid]); $msg = 'Hidden.';
    } elseif ($vid > 0 && $action === 'show') {
        db()->prepare('UPDATE videos SET is_public = 1 WHERE id = ?')->execute([$vid]); $msg = 'Public.';
    }
}
$videos = db()->query(
    'SELECT v.id, v.title, v.views, v.likes_count, v.is_public, v.created_at, u.username
     FROM videos v JOIN users u ON u.id = v.user_id ORDER BY v.id DESC LIMIT 200'
)->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Manage videos</h1><a class="btn-ghost" href="index.php">← Admin</a></div>
<?php if ($msg): ?><div class="flash flash-success"><?= h($msg) ?></div><?php endif; ?>
<div class="panel"><div class="table-wrap">
<table class="data">
<thead><tr><th>ID</th><th>Title</th><th>Channel</th><th>Views</th><th>Public</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($videos as $v): ?>
<tr>
  <td><?= (int)$v['id'] ?></td>
  <td><a href="../watch.php?id=<?= (int)$v['id'] ?>"><?= h($v['title']) ?></a></td>
  <td>@<?= h($v['username']) ?></td>
  <td><?= format_views((int)$v['views']) ?></td>
  <td><?= (int)$v['is_public'] ? 'yes' : 'no' ?></td>
  <td>
    <form method="post" style="display:inline">
      <?= csrf_field() ?>
      <input type="hidden" name="video_id" value="<?= (int)$v['id'] ?>">
      <button name="action" value="<?= (int)$v['is_public']?'hide':'show' ?>" class="btn-ghost"><?= (int)$v['is_public']?'Hide':'Show' ?></button>
      <button name="action" value="delete" class="btn-danger" onclick="return confirm('Delete?')">Delete</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
