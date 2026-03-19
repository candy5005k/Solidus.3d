-- ═══════════════════════════════════════════════════════════════
-- AZALEA / PROPNMORE — LEADS DATABASE SETUP
-- Run this ONCE on your Hostinger MySQL via phpMyAdmin
-- ═══════════════════════════════════════════════════════════════
-- HOW TO RUN:
--   1. Login to Hostinger hPanel → Databases → phpMyAdmin
--   2. Select your database (or create one named: propnmore_leads)
--   3. Click "SQL" tab → paste this entire file → click "Go"
-- ═══════════════════════════════════════════════════════════════

-- Create database (skip if already exists on Hostinger)
-- CREATE DATABASE IF NOT EXISTS propnmore_leads CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE propnmore_leads;

-- ─────────────────────────────────────────────────────────────
-- TABLE: leads
-- One table for ALL forms across ALL sites (Azalea + future)
-- site_name column separates them: 'azalea', 'propnmore', etc.
-- form_type column: 'enquiry', 'floor_plan', 'brochure', 'site_visit'
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `leads` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `site_name`     VARCHAR(50)  NOT NULL DEFAULT 'azalea'   COMMENT 'Which site: azalea, propnmore, etc',
  `form_type`     VARCHAR(50)  NOT NULL DEFAULT 'enquiry'  COMMENT 'enquiry | floor_plan | brochure | site_visit',
  `name`          VARCHAR(150) NOT NULL,
  `phone`         VARCHAR(20)  NOT NULL,
  `email`         VARCHAR(150) DEFAULT NULL,
  `interested_in` VARCHAR(100) DEFAULT NULL                COMMENT '3 BHK | 4 BHK | Both',
  `message`       TEXT         DEFAULT NULL,
  `visit_date`    DATE         DEFAULT NULL                COMMENT 'For site visit bookings',
  `visit_time`    VARCHAR(20)  DEFAULT NULL                COMMENT 'For site visit bookings',
  `ip_address`    VARCHAR(45)  DEFAULT NULL,
  `user_agent`    VARCHAR(500) DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_site`      (`site_name`),
  INDEX `idx_form_type` (`form_type`),
  INDEX `idx_created`   (`created_at`),
  INDEX `idx_phone`     (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- TABLE: floor_plan_unlocks
-- Tracks which floor plan was unlocked + by whom
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `floor_plan_unlocks` (
  `id`          INT(11)     NOT NULL AUTO_INCREMENT,
  `lead_id`     INT(11)     NOT NULL               COMMENT 'FK to leads.id',
  `plan_type`   VARCHAR(50) NOT NULL               COMMENT '3 BHK | 4 BHK | All Floor Plans',
  `unlocked_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- Verify tables were created
-- ─────────────────────────────────────────────────────────────
SHOW TABLES;
