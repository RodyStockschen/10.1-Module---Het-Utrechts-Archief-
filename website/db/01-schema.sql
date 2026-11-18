SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS archive_i18n;
DROP TABLE IF EXISTS archive_items;
DROP TABLE IF EXISTS location_i18n;
DROP TABLE IF EXISTS locations;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS settings;

CREATE TABLE admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locations (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug            VARCHAR(80)  NOT NULL,
    video_start_sec DECIMAL(6,2) NOT NULL DEFAULT 0,
    video_end_sec   DECIMAL(6,2) NOT NULL DEFAULT 0,
    map_x_pct       DECIMAL(5,2) NOT NULL DEFAULT 50,
    map_y_pct       DECIMAL(5,2) NOT NULL DEFAULT 50,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_locations_slug (slug),
    KEY ix_locations_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE location_i18n (
    location_id       INT UNSIGNED NOT NULL,
    lang              ENUM('nl','en') NOT NULL,
    name              VARCHAR(190) NOT NULL,
    short_description TEXT NOT NULL,
    PRIMARY KEY (location_id, lang),
    CONSTRAINT fk_loc_i18n_location FOREIGN KEY (location_id)
        REFERENCES locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE archive_items (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    location_id INT UNSIGNED NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    year        SMALLINT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_archive_location (location_id),
    CONSTRAINT fk_archive_location FOREIGN KEY (location_id)
        REFERENCES locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE archive_i18n (
    archive_item_id INT UNSIGNED NOT NULL,
    lang            ENUM('nl','en') NOT NULL,
    title           VARCHAR(190) NOT NULL,
    description     TEXT NOT NULL,
    PRIMARY KEY (archive_item_id, lang),
    CONSTRAINT fk_arch_i18n_item FOREIGN KEY (archive_item_id)
        REFERENCES archive_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id                 TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
    minimap_focus_x    DECIMAL(5,2) NOT NULL DEFAULT 35.00,
    minimap_focus_y    DECIMAL(5,2) NOT NULL DEFAULT 52.00,
    minimap_focus_zoom DECIMAL(4,2) NOT NULL DEFAULT 2.20,
    CONSTRAINT single_row CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
