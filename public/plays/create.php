<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/PlayModel.php';
require_once __DIR__ . '/../../app/models/GameModel.php';
require_once __DIR__ . '/../../app/models/AuditModel.php';
require_once __DIR__ . '/../../app/validators.php';

$games = GameModel::search('');
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf_token();
    $game_id = (int)($_POST['game_id'] ?? 0);
    $players = (int)($_POST['players_count'] ?? 0);
    $date = trim($_POST['played_at'] ?? '');

    if (!$game_id) { $err = 'Selecciona un juego.'; }
    elseif (($e = validate_players_count($players))) { $err = $e; }
    elseif (($e = validate_date_past_or_present($date))) { $err = $e; }
    else {
        $id = PlayModel::create($game_id, $players, $date, current_user()['id']);
        AuditModel::log('plays', 'create', $id, ['game_id'=>$game_id,'players_count'=>$players,'played_at'=>$date], current_user()['id']);
        header('Location: /plays/list.php'); exit;
    }
}
require_once __DIR__ . '/../includes/header.php';
require_login();
?>
<div class="col-md-6">
  <h4>Añadir partida</h4>
  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
    <div class="mb-3">
      <label class="form-label">Juego</label>
      <select class="form-select" name="game_id" required>
        <option value="">— Elegir —</option>
        <?php foreach ($games as $g): ?>
          <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Número de jugadores</label>
      <input class="form-control" type="number" name="players_count" min="1" max="100" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Fecha</label>
      <input class="form-control" type="date" name="played_at" required>
    </div>
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="/plays/list.php">Volver</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
