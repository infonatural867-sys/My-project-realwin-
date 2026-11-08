<?php
require_once __DIR__ . '/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['ok'=>false,'error'=>'method_not_allowed'],405);
$user=current_user(); $limit=min(100,max(1,(int)($_GET['limit']??30)));
$q=db()->prepare('SELECT id,amount,entry_type,description,created_at FROM point_ledger WHERE user_id=? ORDER BY id DESC LIMIT ?');
$q->bindValue(1,(int)$user['id'],PDO::PARAM_INT); $q->bindValue(2,$limit,PDO::PARAM_INT); $q->execute();
respond(['ok'=>true,'history'=>$q->fetchAll()]);
