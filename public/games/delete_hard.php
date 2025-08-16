<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/GameModel.php';
require_once __DIR__ . '/../../app/models/AuditModel.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$game = GameModel::find($id);
if ($id) {
    GameModel::deleteHard($id);
    AuditModel::log('games', 'delete', $id, ['name' => $game ? $game['name'] : ''], current_user()['id']);
}
header('Location: /games/list.php');
