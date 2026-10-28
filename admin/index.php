<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$nav = 'admin';
$pageTitle = 'Admin';
$stats = [
    'users' => (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'videos' => (int)db()->query('SELECT COUNT(*) FROM videos')->fetchColumn(),
    'comments' => (int)db()->query('SELECT COUNT(*) FROM comments')->fetchColumn(),
    'likes' => (int)db()->query('SELECT COUNT(*) FROM likes')->fetchColumn(),
];
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Admin panel</h1></div>
<div class="video-grid" style="grid-template-columns:repeat(auto-fill,minmax(140px,1fr));margin-bottom:24px">
  <?php foreach (['users'=>'Users','videos'=>'Videos','comments'=>'Comments','likes'=>'Likes'] as $k=>$label): ?>
    <div class="panel" style="text-align:center">
      <div style="font-size:1.75rem;font-weight:700"><?= $stats[$k] ?></div>
      <div class="muted"><?= $label ?></div>
    </div>
  <?php endforeach; ?>
</div>
<div class="panel">
  <h2>Quick links</h2>
  <p>
    <a class="btn-primary" href="users.php">Manage users</a>
    <a class="btn-ghost" href="videos.php">Manage videos</a>
    <a class="btn-ghost" href="../index.php">← Site</a>
  </p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
