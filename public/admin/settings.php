<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/SettingsModel.php';
require_once __DIR__ . '/../../app/models/AuditModel.php';
require_superadmin();

$info = $err = '';
$title = SettingsModel::get('app_name', 'BoardLog');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf_token();
    $new = trim($_POST['app_name'] ?? '');
    if ($new === '') $err = 'El título no puede estar vacío.';
    else {
        SettingsModel::set('app_name', $new);
        AuditModel::log('settings', 'update', 0, ['app_name' => $new], current_user()['id']);
        $title = $new;
        $info = 'Título actualizado.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<h4>Ajustes</h4>
<?php if ($info): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="card">
  <div class="card-body">
    <form method="post" class="row g-2">
      <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
      <div class="col-md-8">
        <label class="form-label">Título del sitio</label>
        <input class="form-control" name="app_name" value="<?= htmlspecialchars($title) ?>" required>
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
