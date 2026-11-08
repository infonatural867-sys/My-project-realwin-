# REALWIN PHP/MySQL API starter

This folder is designed for PHP 8+ shared hosting with MySQL/MariaDB.

1. Create a MySQL database and a database user in your hosting panel.
2. Import `schema.sql` using phpMyAdmin.
3. Copy `config.example.php` to `config.php` on the server and fill in credentials. Do not commit `config.php`.
4. Upload the contents of this folder to a private API directory on your own domain.
5. Open `/api/health.php` to verify PHP and database connectivity.

The current API only exposes a health check. Authentication, sessions, point ledger operations, admin roles, rate limits, and audit logging must be implemented and security-tested before production use. Never trust a point balance sent by the Android client; calculate and record all changes server-side in database transactions.

Points are entertainment-only and have no cash value in this starter.
