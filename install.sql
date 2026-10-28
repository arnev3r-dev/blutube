-- Blutube schema for InfinityFree MySQL
-- phpMyAdmin → select DB if0_43009643_blutube → SQL → paste all → Go

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100) NOT NULL DEFAULT '',
  bio TEXT NULL,
  avatar VARCHAR(500) NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  is_banned TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_role (role),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS videos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  src VARCHAR(1000) NOT NULL,
  thumbnail VARCHAR(1000) NULL,
  duration VARCHAR(20) NOT NULL DEFAULT '0:00',
  category VARCHAR(80) NOT NULL DEFAULT 'General',
  views INT UNSIGNED NOT NULL DEFAULT 0,
  likes_count INT UNSIGNED NOT NULL DEFAULT 0,
  comments_count INT UNSIGNED NOT NULL DEFAULT 0,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_videos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_category (category),
  INDEX idx_views (views),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  video_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_comments_video FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_video (video_id),
  INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS likes (
  user_id INT UNSIGNED NOT NULL,
  video_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, video_id),
  CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_likes_video FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
  subscriber_id INT UNSIGNED NOT NULL,
  channel_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (subscriber_id, channel_id),
  CONSTRAINT fk_sub_subscriber FOREIGN KEY (subscriber_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_sub_channel FOREIGN KEY (channel_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS watch_history (
  user_id INT UNSIGNED NOT NULL,
  video_id INT UNSIGNED NOT NULL,
  watched_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, video_id),
  CONSTRAINT fk_hist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_hist_video FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- admin / admin123  (CHANGE after login)
INSERT INTO users (username, email, password_hash, display_name, role)
VALUES (
  'admin',
  'admin@blutube.local',
  '$2y$10$/6/Ex/n5I2YNqc8UkCxvtuja5SfGEaeolpBCdagfzl3I8qkmIpzBi',
  'Admin',
  'admin'
)
ON DUPLICATE KEY UPDATE role = 'admin';

INSERT INTO users (username, email, password_hash, display_name, role)
VALUES (
  'blender',
  'blender@demo.local',
  '$2y$10$/6/Ex/n5I2YNqc8UkCxvtuja5SfGEaeolpBCdagfzl3I8qkmIpzBi',
  'Blender Foundation',
  'user'
)
ON DUPLICATE KEY UPDATE username = username;

INSERT INTO videos (user_id, title, description, src, thumbnail, duration, category, views, likes_count)
SELECT u.id, 'Big Buck Bunny',
  'Classic open-source animated short by the Blender Foundation.',
  'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
  'https://picsum.photos/seed/blutube1/640/360', '9:56', 'Animation', 12450320, 98210
FROM users u WHERE u.username = 'blender'
AND NOT EXISTS (SELECT 1 FROM videos WHERE title = 'Big Buck Bunny' LIMIT 1);

INSERT INTO videos (user_id, title, description, src, thumbnail, duration, category, views, likes_count)
SELECT u.id, 'Elephants Dream',
  'The world''s first open movie, made entirely with open source software.',
  'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
  'https://picsum.photos/seed/blutube2/640/360', '10:53', 'Animation', 4820190, 41200
FROM users u WHERE u.username = 'blender'
AND NOT EXISTS (SELECT 1 FROM videos WHERE title = 'Elephants Dream' LIMIT 1);

INSERT INTO videos (user_id, title, description, src, thumbnail, duration, category, views, likes_count)
SELECT u.id, 'Sintel',
  'A fantasy short about a girl searching for her lost pet dragon.',
  'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
  'https://picsum.photos/seed/blutube3/640/360', '14:48', 'Fantasy', 8120450, 67300
FROM users u WHERE u.username = 'blender'
AND NOT EXISTS (SELECT 1 FROM videos WHERE title = 'Sintel' LIMIT 1);

INSERT INTO videos (user_id, title, description, src, thumbnail, duration, category, views, likes_count)
SELECT u.id, 'Tears of Steel',
  'Science-fiction short combining live action and CGI by the Blender Institute.',
  'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
  'https://picsum.photos/seed/blutube4/640/360', '12:14', 'Sci-Fi', 3201890, 28900
FROM users u WHERE u.username = 'blender'
AND NOT EXISTS (SELECT 1 FROM videos WHERE title = 'Tears of Steel' LIMIT 1);

INSERT INTO videos (user_id, title, description, src, thumbnail, duration, category, views, likes_count)
SELECT u.id, 'For Bigger Blazes',
  'Short test clip from the Google sample collection.',
  'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
  'https://picsum.photos/seed/blutube5/640/360', '0:15', 'General', 890120, 5400
FROM users u WHERE u.username = 'blender'
AND NOT EXISTS (SELECT 1 FROM videos WHERE title = 'For Bigger Blazes' LIMIT 1);
