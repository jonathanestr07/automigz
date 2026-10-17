-- OPTIONAL: the site creates all of this by itself the first time it runs.
-- Use this file only if you prefer to import it in phpMyAdmin (Import tab).

CREATE DATABASE IF NOT EXISTS `automigz` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `automigz`;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(60) NOT NULL,
  last_name VARCHAR(60) NOT NULL,
  mobile VARCHAR(20) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  pass_hash VARCHAR(255) NOT NULL,
  created_at VARCHAR(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  car_id INT NOT NULL,
  car_name VARCHAR(100) NOT NULL,
  img VARCHAR(255) NOT NULL,
  price INT NOT NULL,
  color_name VARCHAR(60) NOT NULL,
  color_hex VARCHAR(10) NOT NULL,
  days INT NOT NULL,
  qty INT NOT NULL,
  base INT NOT NULL,
  fee INT NOT NULL,
  total INT NOT NULL,
  mode VARCHAR(10) NOT NULL,
  location VARCHAR(120) NOT NULL,
  address VARCHAR(200) NOT NULL DEFAULT '',
  renter VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  pick_date VARCHAR(10) NOT NULL,
  ret_date VARCHAR(10) NOT NULL,
  status VARCHAR(12) NOT NULL DEFAULT 'active',
  created_at VARCHAR(24) NOT NULL,
  cancelled_at VARCHAR(24) NULL,
  INDEX idx_user (user_id),
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
