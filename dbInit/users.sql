CREATE TABLE `users` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(128) NOT NULL,
  `username` VARCHAR(64) NOT NULL,
  `password` VARCHAR(128) NOT NULL,
  `admin` ENUM('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id`)
  ) ENGINE = InnoDB;

-- Create admin user
INSERT INTO `users` (`id`, `email`, `username`, `password`, `admin`)
VALUES (NULL, 'admin@admin.no', 'Admin', 'adminpwd', 'yes');