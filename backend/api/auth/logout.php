<?php
require_once __DIR__ . '/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok'=>false,'error'=>'method_not_allowed'],405);
$token=bearer_token();
if ($token) db()->prepare('DELETE FROM api_sessions WHERE token_hash=?')->execute([hash('sha256',$token)]);
respond(['ok'=>true,'message'=>'logged_out']);
