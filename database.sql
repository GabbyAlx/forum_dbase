CREATE DATABASE IF NOT EXISTS db_data_diri
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_data_diri;

CREATE TABLE IF NOT EXISTS data_diri (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap  VARCHAR(100) NOT NULL,
  tanggal_lahir DATE         NOT NULL,
  hobi          VARCHAR(100) NOT NULL DEFAULT '',
  dibuat_pada   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
