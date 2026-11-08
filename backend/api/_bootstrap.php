<?php
declare(strict_types=1);

$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'error'=>'server_not_configured']);
    exit;
}
$config = require $configFile;
if (!is_array($config)) {
    http_response_code(500);
    exit;
}

$allowedOrigin = $config['allowed_origin'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($allowedOrigin !== '' && hash_equals($allowedOrigin, $origin)) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function db(): PDO {
    static $pdo = null;
    global $config;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']);
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
function respond(array $data, int $status=200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function body_json(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    if (!is_array($data)) respond(['ok'=>false,'error'=>'invalid_json'], 400);
    return $data;
}
function bearer_token(): ?string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$header && function_exists('getallheaders')) {
        $headers = getallheaders();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    return preg_match('/^Bearer\s+([A-Za-z0-9_-]{40,120})$/i', trim($header), $m) ? $m[1] : null;
}
function current_user(bool $admin=false): array {
    $token = bearer_token();
    if (!$token) respond(['ok'=>false,'error'=>'unauthorized'], 401);
    $q = db()->prepare('SELECT u.id,u.username,u.email,u.role,u.status,s.id AS session_id FROM api_sessions s JOIN users u ON u.id=s.user_id WHERE s.token_hash=? AND s.expires_at>UTC_TIMESTAMP() LIMIT 1');
    $q->execute([hash('sha256', $token)]);
    $user = $q->fetch();
    if (!$user || $user['status'] !== 'active') respond(['ok'=>false,'error'=>'unauthorized'], 401);
    if ($admin && $user['role'] !== 'admin') respond(['ok'=>false,'error'=>'forbidden'], 403);
    return $user;
}
function new_session(int $userId): string {
    $token = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
    $q = db()->prepare('INSERT INTO api_sessions(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY))');
    $q->execute([$userId, hash('sha256', $token)]);
    return $token;
}
function rate_limit(string $identity): void {
    $key = hash('sha256', strtolower($identity) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $q = $pdo->prepare('SELECT attempts,window_started_at FROM auth_rate_limits WHERE rate_key=? FOR UPDATE');
        $q->execute([$key]);
        $row = $q->fetch();
        if (!$row) {
            $pdo->prepare('INSERT INTO auth_rate_limits(rate_key,attempts,window_started_at) VALUES(?,1,UTC_TIMESTAMP())')->execute([$key]);
        } else {
            $started = strtotime($row['window_started_at'] . ' UTC');
            if (!$started || time()-$started >= 900) {
                $pdo->prepare('UPDATE auth_rate_limits SET attempts=1,window_started_at=UTC_TIMESTAMP() WHERE rate_key=?')->execute([$key]);
            } elseif ((int)$row['attempts'] >= 8) {
                $pdo->commit();
                respond(['ok'=>false,'error'=>'too_many_attempts','retry_after_seconds'=>max(1,900-(time()-$started))], 429);
            } else {
                $pdo->prepare('UPDATE auth_rate_limits SET attempts=attempts+1 WHERE rate_key=?')->execute([$key]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
