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
        $result = register_user(
            $_POST['username'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            $_POST['display_name'] ?? ''
        );
        if ($result['ok']) {
            login_user($result['user_id']);
            flash('success', 'Account created. Welcome!');
            redirect('index.php');
        }
        $error = $result['error'];
    }
}

$pageTitle = 'Sign up';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
  <h1>Create account</h1>
  <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>
  <form method="post" action="register.php">
    <?= csrf_field() ?>
    <div class="field">
      <label for="username">Username</label>
      <input id="username" name="username" required pattern="[A-Za-z0-9_]{3,30}" value="<?= h($_POST['username'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="display_name">Display name</label>
      <input id="display_name" name="display_name" value="<?= h($_POST['display_name'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="email">Email</label>
      <input id="email" type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="password">Password (min 6)</label>
      <input id="password" type="password" name="password" required minlength="6" autocomplete="new-password">
    </div>
    <div class="form-actions">
      <button type="submit" class="btn-primary">Sign up</button>
      <span class="muted">Have an account? <a href="login.php">Log in</a></span>
    </div>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
