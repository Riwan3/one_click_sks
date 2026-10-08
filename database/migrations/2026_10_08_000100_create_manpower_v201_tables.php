<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Skema Setting Manpower v2.01 (v2_mp_*) disalin persis dari One-Click V1 agar data V1 bisa dipakai apa adanya.
return new class extends Migration
{
    private const TABLES = [
        'v2_mp_csa',
        'v2_mp_categories',
        'v2_mp_units',
        'v2_mp_settings',
        'v2_mp_assignments',
        'v2_mp_released',
        'v2_mp_lain',
    ];

    public function up(): void
    {
        foreach (self::statements() as $sql) {
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (array_reverse(self::TABLES) as $table) {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private static function statements(): array
    {
        return [
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_mp_csa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_mp_csa_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_mp_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `csa_id` int NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `col_pos` tinyint NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_mp_cat` (`csa_id`,`name`),
  KEY `idx_v2_mp_cat_order` (`csa_id`,`col_pos`,`sort_order`),
  CONSTRAINT `fk_v2_mp_cat_csa` FOREIGN KEY (`csa_id`) REFERENCES `v2_mp_csa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_mp_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `is_spare_slot` tinyint(1) NOT NULL DEFAULT '0',
  `is_high_risk` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_mp_unit` (`unit`),
  KEY `idx_v2_mp_unit_order` (`category_id`,`sort_order`),
  CONSTRAINT `fk_v2_mp_unit_cat` FOREIGN KEY (`category_id`) REFERENCES `v2_mp_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_mp_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `work_date` date NOT NULL,
  `shift` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `synced_at` datetime DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_mp_setting` (`work_date`,`shift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_mp_assignments` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `setting_id` int NOT NULL,
  `unit_id` int NOT NULL,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `home_unit` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'manual',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_mp_asg_unit` (`setting_id`,`unit_id`),
  UNIQUE KEY `uq_v2_mp_asg_nrp` (`setting_id`,`nrp`),
  KEY `fk_v2_mp_asg_unit` (`unit_id`),
  CONSTRAINT `fk_v2_mp_asg_setting` FOREIGN KEY (`setting_id`) REFERENCES `v2_mp_settings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_v2_mp_asg_unit` FOREIGN KEY (`unit_id`) REFERENCES `v2_mp_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_mp_released` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `setting_id` int NOT NULL,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_mp_rel` (`setting_id`,`nrp`),
  CONSTRAINT `fk_v2_mp_rel_setting` FOREIGN KEY (`setting_id`) REFERENCES `v2_mp_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_mp_lain` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `setting_id` int NOT NULL,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `label` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'manual',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_mp_lain` (`setting_id`,`nrp`),
  CONSTRAINT `fk_v2_mp_lain_setting` FOREIGN KEY (`setting_id`) REFERENCES `v2_mp_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
        ];
    }
};
