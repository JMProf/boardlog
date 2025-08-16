<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/db.php';
require_login();

$type = $_GET['type'] ?? 'plays';
$filename = $type . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

$out = fopen('php://output', 'w');
$pdo = get_pdo();

if ($type === 'games') {
    fputcsv($out, ['id','name','created_by','created_at','updated_by','updated_at','deleted_by','deleted_at']);
    $rows = $pdo->query("SELECT * FROM games")->fetchAll();
} elseif ($type === 'users') {
    fputcsv($out, ['id','username','role','created_at']);
    $rows = $pdo->query("SELECT id, username, role, created_at FROM users")->fetchAll();
} else {
    fputcsv($out, ['id','game_id','players_count','played_at','created_by','created_at','updated_by','updated_at','deleted_by','deleted_at']);
    $rows = $pdo->query("SELECT * FROM plays")->fetchAll();
}
foreach ($rows as $r) { fputcsv($out, $r); }
fclose($out);
