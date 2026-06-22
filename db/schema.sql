CREATE DATABASE IF NOT EXISTS asset_tracking;
USE asset_tracking;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') DEFAULT 'staff',
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE assets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_tag VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    category_id INT UNSIGNED NOT NULL,
    serial_number VARCHAR(100) UNIQUE NULL,
    location VARCHAR(150) NULL,
    status ENUM('active', 'under_repair', 'disposed', 'lost') DEFAULT 'active',
    purchase_date DATE NULL,
    purchase_cost DECIMAL(10,2) NULL,
    assigned_to VARCHAR(150) NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assets_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_assets_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transaction_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    checked_out_by INT UNSIGNED NULL,
    checked_out_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NOT NULL,
    returned_at TIMESTAMP NULL,
    status ENUM('checked_out', 'returned') DEFAULT 'checked_out',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaction_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    CONSTRAINT fk_transaction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_transaction_checked_out_by FOREIGN KEY (checked_out_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    action ENUM('created', 'updated', 'deleted', 'status_changed') NOT NULL,
    old_value TEXT NULL,
    new_value TEXT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- added another table for the transaction log
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS transaction_logs (
    id              INT             NOT NULL AUTO_INCREMENT,
    asset_id        INT             NOT NULL,
    user_id         INT             NOT NULL,
    checked_out_by  INT             DEFAULT NULL,
    checked_out_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    due_date        DATE            NOT NULL,
    returned_at     DATETIME        DEFAULT NULL,
    status          ENUM('checked_out', 'returned') NOT NULL DEFAULT 'checked_out',
    `condition`     ENUM('good', 'damaged', 'needs_repair') DEFAULT NULL,
    notes           TEXT            DEFAULT NULL,

    PRIMARY KEY (id),
    INDEX idx_tl_asset  (asset_id),
    INDEX idx_tl_user   (user_id),
    INDEX idx_tl_status (status),
    INDEX idx_tl_due    (due_date)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

