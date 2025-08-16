<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/AuditModel.php';
require_once __DIR__ . '/../includes/header.php';
require_superadmin();

$info = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf_token();
    if (($_POST['action'] ?? '') === 'purge') { AuditModel::purgeAll(); $info = 'Historial de auditoría borrado.'; }
}
$logs = AuditModel::list();
?>
<h4>Auditoría</h4>
<?php if ($info): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>

<form method="post" class="mb-3" onsubmit="return confirm('¿Borrar TODO el historial de auditoría?');">
  <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
  <input type="hidden" name="action" value="purge">
  <button class="btn btn-danger">Borrar historial</button>
</form>

<div class="table-responsive">
<table class="table table-striped align-middle">
  <thead><tr><th>Fecha</th><th>Usuario</th><th>Tabla</th><th>Acción</th><th>Registro</th><th>Cambios</th></tr></thead>
  <tbody>
    <?php foreach ($logs as $l): ?>
      <tr>
        <td><?= fmt_datetime($l['created_at']) ?></td>
        <td><?= htmlspecialchars($l['username'] ?? ('#'.$l['actor_user_id'])) ?></td>
        <td><?= htmlspecialchars($l['table_name']) ?></td>
        <td><?= htmlspecialchars($l['action']) ?></td>
        <td><?= (int)$l['record_id'] ?></td>
        <td><pre class="mb-0 small"><?= htmlspecialchars($l['changes_json']) ?></pre></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
