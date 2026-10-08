<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Skema One-Roster (Roster V2 + MONCU) disalin persis dari One-Click V1 agar data V1 bisa dipakai apa adanya.
return new class extends Migration
{
    private const TABLES = [
        'v2_roster_shifts',
        'v2_rosters',
        'v2_roster_sections',
        'v2_roster_details',
        'v2_roster_members',
        'v2_roster_allocations',
        'v2_roster_changelog',
        'v2_analysis_targets',
        'v2_moncu_employees',
        'v2_moncu_periods',
        'v2_moncu_roster_links',
        'v2_moncu_audit',
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
CREATE TABLE IF NOT EXISTS `v2_roster_shifts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'work',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `color` varchar(9) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_shift_code` (`code`),
  KEY `idx_v2_shift_active` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_rosters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `roster_group` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pic_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pic_jabatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `visible_months` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_roster_period` (`roster_group`,`period_start`,`period_end`),
  KEY `idx_v2_roster_period` (`period_start`,`period_end`),
  KEY `idx_v2_roster_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_roster_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `roster_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `target_malam` smallint unsigned DEFAULT NULL,
  `target_siang` smallint unsigned DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_section_name` (`roster_id`,`name`),
  KEY `idx_v2_section_order` (`roster_id`,`sort_order`),
  CONSTRAINT `fk_v2_section_roster` FOREIGN KEY (`roster_id`) REFERENCES `v2_rosters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_roster_details` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `roster_id` int NOT NULL,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `work_date` date NOT NULL,
  `shift_id` int NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_detail_cell` (`roster_id`,`nrp`,`work_date`),
  KEY `idx_v2_detail_nrp_date` (`nrp`,`work_date`),
  KEY `idx_v2_detail_date` (`work_date`),
  KEY `idx_v2_detail_shift` (`shift_id`),
  CONSTRAINT `fk_v2_detail_roster` FOREIGN KEY (`roster_id`) REFERENCES `v2_rosters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_v2_detail_shift` FOREIGN KEY (`shift_id`) REFERENCES `v2_roster_shifts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_roster_members` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `roster_id` int NOT NULL,
  `section_id` int NOT NULL,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kode_off` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `work_pattern` varchar(12) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ROTASI',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_member` (`roster_id`,`nrp`),
  KEY `idx_v2_member_section` (`section_id`,`sort_order`),
  CONSTRAINT `fk_v2_member_roster` FOREIGN KEY (`roster_id`) REFERENCES `v2_rosters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_v2_member_section` FOREIGN KEY (`section_id`) REFERENCES `v2_roster_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_roster_allocations` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `detail_id` bigint NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `section` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_alloc` (`detail_id`,`unit`,`section`),
  KEY `idx_v2_alloc_unit` (`unit`),
  CONSTRAINT `fk_v2_alloc_detail` FOREIGN KEY (`detail_id`) REFERENCES `v2_roster_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_roster_changelog` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `roster_id` int NOT NULL,
  `actor_nrp` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `actor_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `target_nrp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `target_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `summary` varchar(600) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_v2_changelog_roster` (`roster_id`,`created_at`),
  CONSTRAINT `fk_v2_changelog_roster` FOREIGN KEY (`roster_id`) REFERENCES `v2_rosters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_analysis_targets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `roster_id` int NOT NULL,
  `section_id` int NOT NULL,
  `shift_label` enum('SIANG','MALAM') COLLATE utf8mb4_general_ci NOT NULL,
  `target` smallint unsigned NOT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_analysis_target` (`section_id`,`shift_label`),
  KEY `idx_v2_analysis_roster` (`roster_id`),
  CONSTRAINT `fk_v2_analysis_section` FOREIGN KEY (`section_id`) REFERENCES `v2_roster_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_moncu_employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `hiring_date` date DEFAULT NULL,
  `group_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `plan_interval_days` smallint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_moncu_emp_nrp` (`nrp`),
  KEY `idx_v2_moncu_emp_active` (`is_active`),
  KEY `idx_v2_moncu_emp_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_moncu_periods` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `cycle_year` smallint unsigned NOT NULL,
  `period_no` tinyint unsigned NOT NULL,
  `awal` date DEFAULT NULL,
  `tahunan` smallint DEFAULT NULL,
  `izin_resmi` smallint unsigned DEFAULT NULL,
  `akhir` date DEFAULT NULL,
  `induksi` date DEFAULT NULL,
  `plan` date DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_moncu_period` (`nrp`,`cycle_year`,`period_no`),
  KEY `idx_v2_moncu_period_range` (`awal`,`akhir`),
  KEY `idx_v2_moncu_period_induksi` (`induksi`),
  CONSTRAINT `fk_v2_moncu_period_emp` FOREIGN KEY (`nrp`) REFERENCES `v2_moncu_employees` (`nrp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_moncu_roster_links` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `period_id` bigint NOT NULL,
  `detail_id` bigint NOT NULL,
  `work_date` date NOT NULL,
  `kind` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `applied_shift_id` int NOT NULL,
  `prev_shift_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_v2_moncu_link_detail` (`detail_id`),
  KEY `idx_v2_moncu_link_period` (`period_id`),
  CONSTRAINT `fk_v2_moncu_link_detail` FOREIGN KEY (`detail_id`) REFERENCES `v2_roster_details` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_v2_moncu_link_period` FOREIGN KEY (`period_id`) REFERENCES `v2_moncu_periods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
            <<<'SQL'
CREATE TABLE IF NOT EXISTS `v2_moncu_audit` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `nrp` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `cycle_year` smallint unsigned DEFAULT NULL,
  `period_no` tinyint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `old_awal` date DEFAULT NULL,
  `new_awal` date DEFAULT NULL,
  `old_tahunan` smallint DEFAULT NULL,
  `new_tahunan` smallint DEFAULT NULL,
  `old_izin_resmi` smallint unsigned DEFAULT NULL,
  `new_izin_resmi` smallint unsigned DEFAULT NULL,
  `new_akhir` date DEFAULT NULL,
  `new_induksi` date DEFAULT NULL,
  `new_plan` date DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `actor` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_v2_moncu_audit_nrp` (`nrp`,`created_at`),
  KEY `idx_v2_moncu_audit_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL,
        ];
    }
};
