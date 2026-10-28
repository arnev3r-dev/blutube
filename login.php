<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (current_user()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid session. Please try again.';
    } else {
        $result = attempt_login($_POST['login'] ?? '', $_POST['password'] ?? '');
        if ($result['ok']) {
            flash('success', 'Welcome back!');
            redirect('index.php');
        }
        $error = $result['error'];
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
  <h1>Log in</h1>
  <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>
  <form method="post" action="login.php">
    <?= csrf_field() ?>
    <div class="field">
      <label for="login">Username or email</label>
      <input id="login" name="login" required autocomplete="username" value="<?= h($_POST['login'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="password">Password</label>
      <input id="password" type="password" name="password" required autocomplete="current-password">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn-primary">Log in</button>
      <span class="muted">No account? <a href="register.php">Sign up</a></span>
    </div>
  </form>
  <p class="muted" style="margin-top:16px">Default admin: <code>admin</code> / <code>admin123</code></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
