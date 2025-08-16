<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/UserModel.php';
require_once __DIR__ . '/../includes/header.php';
require_login();

$info = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf_token();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    if ($new === '') { $err = 'La nueva contraseña no puede estar vacía.'; }
    else {
        // Traer hash actual del usuario
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute([':id' => current_user()['id']]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($current, $hash)) {
            $err = 'La contraseña actual no es correcta.';
        } else {
            UserModel::resetPassword((int)current_user()['id'], $new);
            $info = 'Contraseña actualizada.';
        }
    }
}
?>
<h4>Mi cuenta</h4>
<?php if ($info): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="card">
  <div class="card-body">
    <form method="post" class="row g-3">
      <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
      <div class="col-md-6">
        <label class="form-label">Contraseña actual</label>
        <input class="form-control" type="password" name="current_password" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Nueva contraseña</label>
        <input class="form-control" type="password" name="new_password" required>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
