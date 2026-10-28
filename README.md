# Blutube (PHP + MySQL)

YouTube-style site for **InfinityFree**.

## Fix “site doesn’t open”

1. Upload **all** files into `htdocs` (not a nested folder only).
2. Open **`https://YOUR-SITE/check.php`** — this shows what is broken.
3. In phpMyAdmin → database `if0_43009643_blutube` → SQL → paste **`install.sql`** → Go.
4. Reload `check.php` until everything is green.
5. Open `index.php`.

## Default login

- Username: `admin`
- Password: `admin123`

## Files that must exist

```
htdocs/
  check.php          ← open this first
  config.php
  index.php
  install.sql
  login.php, register.php, watch.php, studio.php, ...
  includes/          (db.php, auth.php, functions.php, header.php, footer.php)
  api/               (like.php, subscribe.php, comment.php)
  admin/             (index.php, users.php, videos.php)
  assets/            (style.css, app.js)
```

## Config

`config.php` already has your InfinityFree MySQL settings.
