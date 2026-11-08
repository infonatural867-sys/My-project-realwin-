-- REALWIN social entertainment modules. No cash, payment, wagering or redemption tables.
CREATE TABLE IF NOT EXISTS games (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(80) NOT NULL UNIQUE,
 title_en VARCHAR(120) NOT NULL, title_bn VARCHAR(120) NOT NULL,
 description_en VARCHAR(500) NOT NULL DEFAULT '', description_bn VARCHAR(500) NOT NULL DEFAULT '',
 category VARCHAR(60) NOT NULL DEFAULT 'casual',
 status ENUM('draft','active','paused','archived') NOT NULL DEFAULT 'draft',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS tournaments (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, title VARCHAR(160) NOT NULL, description TEXT NULL,
 status ENUM('draft','legal_review','approved','open','closed','completed','cancelled') NOT NULL DEFAULT 'draft',
 starts_at DATETIME NULL, ends_at DATETIME NULL, rules_version VARCHAR(40) NULL, created_by BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_tournament_status_start(status,starts_at),
 CONSTRAINT fk_tournament_admin FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS tournament_entries (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, tournament_id BIGINT UNSIGNED NOT NULL, user_id BIGINT UNSIGNED NOT NULL,
 status ENUM('registered','withdrawn','disqualified','completed') NOT NULL DEFAULT 'registered',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_tournament_user(tournament_id,user_id),
 CONSTRAINT fk_entry_tournament FOREIGN KEY(tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
 CONSTRAINT fk_entry_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS notifications (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, title VARCHAR(160) NOT NULL,
 body VARCHAR(1000) NOT NULL, type ENUM('system','account','safety','community') NOT NULL DEFAULT 'system',
 read_at DATETIME NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_notification_user_created(user_id,created_at),
 CONSTRAINT fk_notification_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS support_tickets (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 subject VARCHAR(180) NOT NULL, message TEXT NOT NULL,
 status ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_ticket_status_created(status,created_at),
 CONSTRAINT fk_ticket_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS complaints (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL,
 category VARCHAR(80) NOT NULL, description TEXT NOT NULL,
 status ENUM('received','under_review','resolved','rejected') NOT NULL DEFAULT 'received',
 resolution_note TEXT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, resolved_at DATETIME NULL,
 INDEX idx_complaint_status_created(status,created_at),
 CONSTRAINT fk_complaint_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS consent_records (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 consent_type VARCHAR(80) NOT NULL, policy_version VARCHAR(40) NOT NULL, granted TINYINT(1) NOT NULL,
 ip_hash CHAR(64) NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_consent_user_type(user_id,consent_type),
 CONSTRAINT fk_consent_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS admin_audit_logs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, admin_user_id BIGINT UNSIGNED NOT NULL,
 action VARCHAR(100) NOT NULL, target_type VARCHAR(60) NULL, target_id VARCHAR(80) NULL,
 details_json JSON NULL, ip_hash CHAR(64) NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_admin_created(admin_user_id,created_at), INDEX idx_audit_target(target_type,target_id),
 CONSTRAINT fk_audit_admin FOREIGN KEY(admin_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
