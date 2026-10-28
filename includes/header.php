<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$user = current_user();
$flashes = get_flashes();
$pageTitle = $pageTitle ?? APP_NAME;

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$inAdmin = (bool)preg_match('#/admin$#', $scriptDir);
$ROOT = $inAdmin ? '..' : '.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($pageTitle) ?> · <?= h(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= h($ROOT) ?>/assets/style.css">
</head>
<body>
<header class="header">
  <div class="header-left">
    <a href="<?= h($ROOT) ?>/index.php" class="logo">
      <span class="logo-mark">▶</span>
      <span class="logo-text"><?= h(APP_NAME) ?></span>
    </a>
  </div>
  <form class="search-form" action="<?= h($ROOT) ?>/search.php" method="get">
    <input type="search" name="q" placeholder="Search" value="<?= h($_GET['q'] ?? '') ?>" autocomplete="off">
    <button type="submit" aria-label="Search">⌕</button>
  </form>
  <div class="header-right">
    <?php if ($user): ?>
      <a class="btn-ghost" href="<?= h($ROOT) ?>/studio.php">+ Create</a>
      <?php if ($user['role'] === 'admin'): ?>
        <a class="btn-ghost" href="<?= h($ROOT) ?>/admin/">Admin</a>
      <?php endif; ?>
      <a class="avatar-link" href="<?= h($ROOT) ?>/channel.php?u=<?= urlencode($user['username']) ?>" title="<?= h($user['display_name']) ?>">
        <img src="<?= h(avatar_url($user['avatar'], $user['display_name'])) ?>" alt="" width="32" height="32">
      </a>
      <a class="btn-ghost" href="<?= h($ROOT) ?>/logout.php">Log out</a>
    <?php else: ?>
      <a class="btn-ghost" href="<?= h($ROOT) ?>/login.php">Log in</a>
      <a class="btn-primary" href="<?= h($ROOT) ?>/register.php">Sign up</a>
    <?php endif; ?>
  </div>
</header>
<div class="layout">
  <aside class="sidebar">
    <nav>
      <a href="<?= h($ROOT) ?>/index.php" class="<?= ($nav ?? '') === 'home' ? 'active' : '' ?>">Home</a>
      <a href="<?= h($ROOT) ?>/trending.php" class="<?= ($nav ?? '') === 'trending' ? 'active' : '' ?>">Trending</a>
      <a href="<?= h($ROOT) ?>/subscriptions.php" class="<?= ($nav ?? '') === 'subs' ? 'active' : '' ?>">Subscriptions</a>
      <a href="<?= h($ROOT) ?>/history.php" class="<?= ($nav ?? '') === 'history' ? 'active' : '' ?>">History</a>
      <a href="<?= h($ROOT) ?>/studio.php" class="<?= ($nav ?? '') === 'studio' ? 'active' : '' ?>">Studio</a>
      <?php if ($user && $user['role'] === 'admin'): ?>
        <a href="<?= h($ROOT) ?>/admin/" class="<?= ($nav ?? '') === 'admin' ? 'active' : '' ?>">Admin panel</a>
      <?php endif; ?>
    </nav>
  </aside>
  <main class="main">
    <?php foreach ($flashes as $f): ?>
      <div class="flash flash-<?= h($f['type']) ?>"><?= h($f['message']) ?></div>
    <?php endforeach; ?>
