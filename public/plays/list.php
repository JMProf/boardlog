<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/PlayModel.php';
require_once __DIR__ . '/../../app/models/GameModel.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$games = GameModel::search('');
$gid = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;
$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;
$plays = PlayModel::search($gid ?: null, $from ?: null, $to ?: null);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Partidas</h4>
  <a href="/plays/create.php" class="btn btn-primary">Añadir partida</a>
</div>

<form class="row g-2 mb-3">
  <div class="col-md-4">
    <label class="form-label">Juego</label>
    <select class="form-select" name="game_id">
      <option value="">(Todos)</option>
      <?php foreach ($games as $g): ?>
        <option value="<?= (int)$g['id'] ?>" <?= $gid===(int)$g['id']?'selected':'' ?>><?= htmlspecialchars($g['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($from ?? '') ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($to ?? '') ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label d-block">&nbsp;</label>
    <button class="btn btn-outline-secondary w-100">Filtrar</button>
  </div>
</form>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead><tr><th>Fecha</th><th>Juego</th><th>Jugadores</th><th class="text-end">Acciones</th></tr></thead>
  <tbody>
    <?php foreach ($plays as $p): ?>
      <tr>
        <td><?= fmt_date($p['played_at']) ?></td>
        <td><?= htmlspecialchars($p['game_name']) ?></td>
        <td><?= (int)$p['players_count'] ?></td>
        
        <td class="text-end">
  <a class="btn btn-sm btn-outline-secondary" href="/plays/edit.php?id=<?= (int)$p['id'] ?>">Editar</a>
  <a class="btn btn-sm btn-outline-danger" href="/plays/delete.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('¿Borrar definitivamente?')">Borrar</a>
</td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
