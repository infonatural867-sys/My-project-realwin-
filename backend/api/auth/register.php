<?php
require_once __DIR__ . '/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok'=>false,'error'=>'method_not_allowed'],405);
$data = body_json();
$username = trim((string)($data['username'] ?? ''));
$email = strtolower(trim((string)($data['email'] ?? '')));
$password = (string)($data['password'] ?? '');
if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) respond(['ok'=>false,'error'=>'invalid_username'],422);
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) respond(['ok'=>false,'error'=>'invalid_email'],422);
if (strlen($password)<10 || strlen($password)>200) respond(['ok'=>false,'error'=>'password_must_be_10_to_200_chars'],422);
if (($data['terms_accepted'] ?? false) !== true) respond(['ok'=>false,'error'=>'terms_acceptance_required'],422);
rate_limit($email);
$pdo=db();
try {
    $pdo->beginTransaction();
    $q=$pdo->prepare("INSERT INTO users(username,email,password_hash) VALUES(?,?,?)");
    $q->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT)]);
    $id=(int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO point_wallets(user_id,balance) VALUES(?,0)')->execute([$id]);
    $ipHash = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $pdo->prepare('INSERT INTO consent_records(user_id,consent_type,policy_version,granted,ip_hash) VALUES(?,?,?,?,?)')->execute([$id,'terms_privacy','draft-1',1,$ipHash]);
    $pdo->commit();
    $token=new_session($id);
    respond(['ok'=>true,'token'=>$token,'token_type'=>'Bearer','expires_in'=>2592000,'user'=>['id'=>$id,'username'=>$username,'email'=>$email,'role'=>'user'],'points'=>0],201);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    if ((string)$e->getCode()==='23000') respond(['ok'=>false,'error'=>'username_or_email_already_exists'],409);
    respond(['ok'=>false,'error'=>'registration_failed'],500);
}
