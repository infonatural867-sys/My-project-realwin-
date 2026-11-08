<?php
require_once __DIR__ . '/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['ok'=>false,'error'=>'method_not_allowed'],405);
$user=current_user();
$q=db()->prepare('SELECT balance FROM point_wallets WHERE user_id=?');
$q->execute([$user['id']]); $wallet=$q->fetch();
respond(['ok'=>true,'user'=>['id'=>(int)$user['id'],'username'=>$user['username'],'email'=>$user['email'],'role'=>$user['role']],'points'=>(int)($wallet['balance']??0)]);
