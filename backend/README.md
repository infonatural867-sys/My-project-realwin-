# REALWIN PHP/MySQL API

Designed for PHP 8.1+ shared hosting with MySQL/MariaDB (including cPanel-style hosting).

## Install

1. Create a new MySQL database and database user in your hosting panel.
2. Open phpMyAdmin and import schema.sql once into an empty database. If the starter schema is already installed, do not import it again. Run migrations/001_auth_tables.sql only if authentication tables are missing, then run migrations/002_platform_modules.sql once to add game, tournament, support, consent and audit tables.
3. Copy config.example.php to config.php in this folder on the server. Fill in database details. Keep config.php private; it is excluded by .htaccess and .gitignore.
4. Upload the api/ folder and .htaccess to your hosting directory. Ensure HTTPS is enabled.
5. Visit /api/health.php to test database connectivity.

## API routes

All request/response bodies use JSON. Login and registration return a random bearer token. Store it securely on the device and send Authorization: Bearer TOKEN on protected routes. Tokens expire after 30 days; logout revokes the current token.

| Method | Route | Purpose |
|---|---|---|
| POST | /api/auth/register.php | Create account (username, email, password) |
| POST | /api/auth/login.php | Login (identity, password; identity can be email or username) |
| GET | /api/auth/me.php | Current account and points |
| POST | /api/auth/logout.php | Revoke current token |
| GET | /api/points/history.php | Current user's point ledger |
| GET | /api/admin/users.php | Admin-only user list |
| POST | /api/admin/adjust-points.php | Admin-only point adjustment |

### Example registration

    {"username":"player_01","email":"player@example.com","password":"use-a-long-unique-password"}

### Example login

    {"identity":"player@example.com","password":"use-a-long-unique-password"}

### Admin point adjustment

Send as an admin bearer token:

    {"user_id":12,"amount":25,"reason":"Approved non-cash test reward"}

A negative amount deducts points. The API rejects a balance below zero and records each adjustment in point_ledger.

## Make your first admin

After registering your own account, run this SQL manually in phpMyAdmin, replacing the email with the exact account email you registered:

    UPDATE users SET role='admin' WHERE email='YOUR_REGISTERED_EMAIL';

Never expose this SQL or database credentials in the Android app. Do not make a public admin signup route.

## Security notes

- Passwords use PHP password_hash / password_verify.
- Bearer tokens are random and only SHA-256 hashes are stored in MySQL.
- Login/registration attempts have a basic per-identity/IP throttle.
- SQL uses prepared statements; point changes are transaction-protected.
- Admin routes check the server-side role.
- For production, add email verification, password reset, stronger abuse monitoring, periodic expired-session cleanup, security review, and backups.
- Do not trust balances from the app; the server owns the ledger.
- Points are non-cash entertainment points only. No deposits, withdrawals, betting, cash prizes, or cash-out features are implemented.
