-- Production Database Indexes for OneClick Laravel 13
-- Run: mysql one_click_laravel < database/optimizations/indexes.sql

-- High-Query Tables Indexes

-- Pembinaan Documents Table
ALTER TABLE pembinaan_documents ADD INDEX idx_employee_created (employee_id, created_at);
ALTER TABLE pembinaan_documents ADD INDEX idx_tanggal_status (tanggal, status);
ALTER TABLE pembinaan_documents ADD INDEX idx_status (status);

-- MPP Unit Photos Table
ALTER TABLE mpp_unit_photos ADD INDEX idx_jenis_tanggal_unit (jenis, tanggal, unit_code);
ALTER TABLE mpp_unit_photos ADD INDEX idx_created_by (created_by);
ALTER TABLE mpp_unit_photos ADD INDEX idx_tanggal (tanggal);

-- MPP Daily Adjustments Table
ALTER TABLE mpp_daily_adjustments ADD INDEX idx_tanggal_shift_kategori (tanggal, shift, kategori);
ALTER TABLE mpp_daily_adjustments ADD INDEX idx_tanggal (tanggal);

-- Coaching Clinics Table
ALTER TABLE coaching_clinics ADD INDEX idx_status_tanggal (status, tanggal_coaching);
ALTER TABLE coaching_clinics ADD INDEX idx_coach_id (coach_id);
ALTER TABLE coaching_clinics ADD INDEX idx_tanggal_coaching (tanggal_coaching);

-- Employees Table (Critical)
ALTER TABLE employees ADD INDEX idx_nrp (nrp);
ALTER TABLE employees ADD INDEX idx_status (status);
ALTER TABLE employees ADD INDEX idx_departemen (departemen);
ALTER TABLE employees ADD INDEX idx_status_departemen (status, departemen);

-- Sessions Table (for session queries)
ALTER TABLE sessions ADD INDEX idx_user_id_last_activity (user_id, last_activity);
ALTER TABLE sessions ADD INDEX idx_last_activity (last_activity);

-- Job Logs Table
ALTER TABLE job_logs ADD INDEX idx_status_created (status, created_at);
ALTER TABLE job_logs ADD INDEX idx_created_at (created_at);

-- SPDK Logs Table
ALTER TABLE spdk_logs ADD INDEX idx_status_created (status, created_at);
ALTER TABLE spdk_logs ADD INDEX idx_created_at (created_at);

-- MPP Kesiapan Operators
ALTER TABLE mpp_kesiapan_operators ADD INDEX idx_tanggal_operator (tanggal, operator_id);
ALTER TABLE mpp_kesiapan_operators ADD INDEX idx_status (status);

-- MPP Roster Data
ALTER TABLE mpp_roster_data ADD INDEX idx_tanggal_shift (tanggal, shift);
ALTER TABLE mpp_roster_data ADD INDEX idx_employee_id (employee_id);

-- Users Table
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_employee_id (employee_id);

-- Roles Table
ALTER TABLE roles ADD INDEX idx_name (name);

-- Covering Indexes for Frequent Queries
ALTER TABLE pembinaan_documents ADD INDEX idx_covering_employee_list (employee_id, status, created_at, name);
ALTER TABLE employees ADD INDEX idx_covering_list (id, name, nrp, status, departemen);
ALTER TABLE coaching_clinics ADD INDEX idx_covering_list (id, status, tanggal_coaching, coach_id, judul);

-- Analyze table statistics for query optimizer
ANALYZE TABLE pembinaan_documents;
ANALYZE TABLE mpp_unit_photos;
ANALYZE TABLE mpp_daily_adjustments;
ANALYZE TABLE coaching_clinics;
ANALYZE TABLE employees;
ANALYZE TABLE sessions;
ANALYZE TABLE job_logs;
ANALYZE TABLE spdk_logs;
ANALYZE TABLE mpp_kesiapan_operators;
ANALYZE TABLE mpp_roster_data;
ANALYZE TABLE users;
ANALYZE TABLE roles;
