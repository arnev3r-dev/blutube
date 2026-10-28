<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$nav = 'admin';
$pageTitle = 'Users · Admin';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $uid = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($uid > 0 && $uid !== (int)$admin['id']) {
        if ($action === 'ban') { db()->prepare('UPDATE users SET is_banned = 1 WHERE id = ?')->execute([$uid]); $msg = 'Banned.'; }
        elseif ($action === 'unban') { db()->prepare('UPDATE users SET is_banned = 0 WHERE id = ?')->execute([$uid]); $msg = 'Unbanned.'; }
        elseif ($action === 'make_admin') { db()->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$uid]); $msg = 'Promoted.'; }
        elseif ($action === 'make_user') { db()->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$uid]); $msg = 'Demoted.'; }
        elseif ($action === 'delete') { db()->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]); $msg = 'Deleted.'; }
    }
}
$users = db()->query('SELECT id, username, display_name, email, role, is_banned, created_at FROM users ORDER BY id DESC LIMIT 200')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><h1>Manage users</h1><a class="btn-ghost" href="index.php">← Admin</a></div>
<?php if ($msg): ?><div class="flash flash-success"><?= h($msg) ?></div><?php endif; ?>
<div class="panel"><div class="table-wrap">
<table class="data">
<thead><tr><th>ID</th><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
  <td><?= (int)$u['id'] ?></td>
  <td>@<?= h($u['username']) ?> · <?= h($u['display_name']) ?></td>
  <td><?= h($u['email']) ?></td>
  <td><?= $u['role'] === 'admin' ? '<span class="badge badge-admin">admin</span>' : '<span class="badge">user</span>' ?></td>
  <td><?= (int)$u['is_banned'] ? '<span class="badge badge-banned">banned</span>' : 'active' ?></td>
  <td>
    <?php if ((int)$u['id'] === (int)$admin['id']): ?>
      <span class="muted">you</span>
    <?php else: ?>
      <form method="post" style="display:inline">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
        <button name="action" value="<?= (int)$u['is_banned'] ? 'unban' : 'ban' ?>" class="btn-ghost"><?= (int)$u['is_banned'] ? 'Unban' : 'Ban' ?></button>
        <button name="action" value="<?= $u['role']==='admin'?'make_user':'make_admin' ?>" class="btn-ghost"><?= $u['role']==='admin'?'Demote':'Make admin' ?></button>
        <button name="action" value="delete" class="btn-danger" onclick="return confirm('Delete user?')">Delete</button>
      </form>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
