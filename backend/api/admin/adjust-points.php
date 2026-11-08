<?php
require_once __DIR__ . '/../_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok'=>false,'error'=>'method_not_allowed'],405);
$admin=current_user(true); $data=body_json();
$userId=(int)($data['user_id']??0); $amount=(int)($data['amount']??0); $reason=trim((string)($data['reason']??''));
if ($userId<1 || $amount===0 || abs($amount)>100000 || $reason==='' || strlen($reason)>200) respond(['ok'=>false,'error'=>'invalid_adjustment'],422);
$pdo=db(); $pdo->beginTransaction();
try {
    $q=$pdo->prepare('SELECT balance FROM point_wallets WHERE user_id=? FOR UPDATE'); $q->execute([$userId]); $wallet=$q->fetch();
    if (!$wallet) { $pdo->rollBack(); respond(['ok'=>false,'error'=>'user_not_found'],404); }
    $new=(int)$wallet['balance']+$amount;
    if ($new<0) { $pdo->rollBack(); respond(['ok'=>false,'error'=>'insufficient_points'],409); }
    $pdo->prepare('UPDATE point_wallets SET balance=? WHERE user_id=?')->execute([$new,$userId]);
    $pdo->prepare("INSERT INTO point_ledger(user_id,amount,entry_type,description,reference_id) VALUES(?,?,'admin_adjustment',?,?)")->execute([$userId,$amount,$reason,'admin-'.$admin['id']]);
    $details = json_encode(['amount'=>$amount,'reason'=>$reason], JSON_UNESCAPED_SLASHES);
    $ipHash = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $pdo->prepare('INSERT INTO admin_audit_logs(admin_user_id,action,target_type,target_id,details_json,ip_hash) VALUES(?,?,?,?,?,?)')
        ->execute([(int)$admin['id'],'points.adjust','user',(string)$userId,$details,$ipHash]);
    $pdo->commit();
    respond(['ok'=>true,'user_id'=>$userId,'balance'=>$new]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond(['ok'=>false,'error'=>'adjustment_failed'],500);
}
