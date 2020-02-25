CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL UNIQUE,
  `password` varchar(128) NOT NULL,
  `type` enum('student','teacher','admin') NOT NULL,
  `verified` bit(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `videos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `location` varchar(255) NOT NULL,
  `mime` varchar(255) NOT NULL,
  `description` varchar(128),
  `subject` varchar(64),
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `upload_time` timestamp NOT NULL,
  `uploaded_by` bigint(20) NOT NULL,
  `thumbnail` varchar(255),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `comments` (
  `user_id` bigint(20) NOT NULL,
  `video_id` bigint(20) NOT NULL,
  `time` timestamp NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`user_id`,`video_id`,`time`),
  FOREIGN KEY (`user_id`) REFERENCES users (id),
  FOREIGN KEY (`video_id`) REFERENCES videos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `playlists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(512),
  `thumbnail` longblob,
  `created_by` bigint(20) NOT NULL,
  `time_created` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `playlist_videos` (
  `playlist_id` bigint(20) NOT NULL,
  `video_id` bigint(20) NOT NULL,
  PRIMARY KEY (`playlist_id`,`video_id`),
  FOREIGN KEY (`playlist_id`) REFERENCES playlists (id),
  FOREIGN KEY (`video_id`) REFERENCES videos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `subscriptions` (
  `playlist_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  PRIMARY KEY (`playlist_id`,`user_id`),
  FOREIGN KEY (`playlist_id`) REFERENCES playlists (id),
  FOREIGN KEY (`user_id`) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `rating` (
  `video_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `rating` enum('1','2','3','4','5'),
  `liked` bit(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`video_id`,`user_id`),
  FOREIGN KEY (`video_id`) REFERENCES videos (id),
  FOREIGN KEY (`user_id`) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Create admin user --
INSERT INTO users (name, email, password, type, verified) 
VALUES ('Admin User', 'admin@admin.no', '$2y$12$rciYrOAeONqVU7HjS6y32eRcLMJl16P/SRXJrTEm/Gu7litDRhyAa', 'admin', 1);