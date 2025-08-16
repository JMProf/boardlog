<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/GameModel.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$games = GameModel::search('');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Juegos</h4>
  <a href="/games/create.php" class="btn btn-primary">Añadir juego</a>
</div>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead><tr><th>Nombre</th><th>Creado</th><th class="text-end">Acciones</th></tr></thead>
  <tbody>
    <?php foreach ($games as $g): ?>
      <tr>
        <td><?= htmlspecialchars($g['name']) ?></td>
        <td><?= fmt_date($g['created_at']) ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-secondary" href="/games/edit.php?id=<?= (int)$g['id'] ?>">Editar</a>
          <a class="btn btn-sm btn-outline-danger" href="/games/delete.php?id=<?= (int)$g['id'] ?>" onclick="return confirm('¿Borrar definitivamente?')">Borrar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
