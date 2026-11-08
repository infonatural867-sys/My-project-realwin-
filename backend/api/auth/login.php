<?php
require_once __DIR__ . '/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok'=>false,'error'=>'method_not_allowed'],405);
$data=body_json();
$identity=strtolower(trim((string)($data['identity'] ?? $data['email'] ?? '')));
$password=(string)($data['password'] ?? '');
if ($identity==='' || strlen($identity)>190 || $password==='') respond(['ok'=>false,'error'=>'credentials_required'],422);
rate_limit($identity);
$q=db()->prepare('SELECT id,username,email,password_hash,role,status FROM users WHERE email=? OR username=? LIMIT 1');
$q->execute([$identity,$identity]);
$user=$q->fetch();
if (!$user || !password_verify($password,$user['password_hash'])) respond(['ok'=>false,'error'=>'invalid_credentials'],401);
if ($user['status']!=='active') respond(['ok'=>false,'error'=>'account_suspended'],403);
if (password_needs_rehash($user['password_hash'],PASSWORD_DEFAULT)) {
    db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),$user['id']]);
}
$token=new_session((int)$user['id']);
$w=db()->prepare('SELECT balance FROM point_wallets WHERE user_id=?');
$w->execute([$user['id']]); $wallet=$w->fetch();
respond(['ok'=>true,'token'=>$token,'token_type'=>'Bearer','expires_in'=>2592000,'user'=>['id'=>(int)$user['id'],'username'=>$user['username'],'email'=>$user['email'],'role'=>$user['role']],'points'=>(int)($wallet['balance']??0)]);
