<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/models/PlayModel.php';

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

header('Content-Type: application/json; charset=utf-8');
echo json_encode(PlayModel::averages($from ?: null, $to ?: null), JSON_UNESCAPED_UNICODE);
