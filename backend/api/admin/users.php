<?php
require_once __DIR__ . '/../_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['ok'=>false,'error'=>'method_not_allowed'],405);
current_user(true);
$page=max(1,(int)($_GET['page']??1)); $limit=min(50,max(1,(int)($_GET['limit']??20))); $offset=($page-1)*$limit;
$q=db()->prepare('SELECT u.id,u.username,u.email,u.role,u.status,u.created_at,COALESCE(w.balance,0) AS points FROM users u LEFT JOIN point_wallets w ON w.user_id=u.id ORDER BY u.id DESC LIMIT ? OFFSET ?');
$q->bindValue(1,$limit,PDO::PARAM_INT); $q->bindValue(2,$offset,PDO::PARAM_INT); $q->execute();
respond(['ok'=>true,'page'=>$page,'limit'=>$limit,'users'=>$q->fetchAll()]);
