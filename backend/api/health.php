<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'service' => 'REALWIN API', 'database' => 'not_configured']);
    exit;
}
$config = require $configFile;
try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']);
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
    $pdo->query('SELECT 1');
    echo json_encode(['ok' => true, 'service' => 'REALWIN API', 'database' => 'connected']);
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'service' => 'REALWIN API', 'database' => 'unavailable']);
}
