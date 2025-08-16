<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/PlayModel.php';
require_once __DIR__ . '/../../app/models/AuditModel.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$play = PlayModel::find($id);
if (!$play) { header('Location: /plays/list.php'); exit; }

PlayModel::deleteHard($id);
AuditModel::log('plays', 'delete', $id, [], current_user()['id']);
header('Location: /plays/list.php');
