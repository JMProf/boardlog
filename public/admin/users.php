<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/UserModel.php';
require_superadmin();

$info = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf_token();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $u = trim($_POST['username'] ?? '');
        $p = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'socio';
        if ($u === '' || $p === '') $err = 'Usuario y contraseña son obligatorios.';
        else {
            try { UserModel::create($u, $p, $role); $info = 'Usuario creado.'; }
            catch (Throwable $e) { $err = 'No se pudo crear (¿usuario existente?).'; }
        }
    } elseif ($action === 'reset') {
        $id = (int)($_POST['id'] ?? 0);
        $p = $_POST['password'] ?? '';
        if (!$id || $p==='') $err = 'Faltan datos.';
        else {
            try { UserModel::resetPassword($id, $p); $info = 'Contraseña actualizada.'; }
            catch (Throwable $e) { $err = 'No se pudo actualizar la contraseña.'; }
        }
    }
}
$users = UserModel::all();
require_once __DIR__ . '/../includes/header.php';
?>
<h4>Usuarios</h4>
<?php if ($info): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="card mb-4">
  <div class="card-body">
    <h6 class="mb-3">Crear usuario</h6>
    <form method="post" class="row g-2">
      <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
      <input type="hidden" name="action" value="create">
      <div class="col-md-4"><input class="form-control" name="username" placeholder="Usuario" required></div>
      <div class="col-md-4"><input class="form-control" type="password" name="password" placeholder="Contraseña" required></div>
      <div class="col-md-2">
        <select class="form-select" name="role">
          <option value="socio">socio</option>
          <option value="superadmin">superadmin</option>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Crear</button></div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h6 class="mb-3">Usuarios existentes</h6>
    <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Creado</th><th>Resetear contraseña</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><?= fmt_date($u['created_at']) ?></td>
            <td>
<?php if ((int)$u['id'] === (int)current_user()['id']): ?>
  <a class="btn btn-sm btn-outline-secondary" href="/account/password.php">Cambiar en “Mi cuenta”</a>
<?php else: ?>
  <form method="post" class="d-flex gap-2">
    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="action" value="reset">
    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
    <input class="form-control" type="password" name="password" placeholder="Nueva contraseña" required>
    <button class="btn btn-sm btn-outline-primary">Actualizar</button>
  </form>
<?php endif; ?>
</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
