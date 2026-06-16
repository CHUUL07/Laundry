-- ============================================
-- Laundry-IN — Database Structure
-- Database: kampusin_db
-- ============================================

USE kampusin_db;

-- Table: admins
CREATE TABLE IF NOT EXISTS `admins` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(50)  NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jenis_layanan
CREATE TABLE IF NOT EXISTS `jenis_layanan` (
    `id`               INT(11)                        NOT NULL AUTO_INCREMENT,
    `nama_layanan`     VARCHAR(100)                   NOT NULL,
    `kategori`         ENUM('express', 'reguler')     NOT NULL,
    `harga`            INT(11)                        NOT NULL,
    `satuan_harga`     ENUM('kg', 'item', 'paket')   NOT NULL DEFAULT 'kg',
    `estimasi_durasi`  VARCHAR(50)                    NOT NULL,
    `deskripsi`        TEXT                           NULL DEFAULT NULL,
    `created_at`       DATETIME                       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME                       NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`       DATETIME                       NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
