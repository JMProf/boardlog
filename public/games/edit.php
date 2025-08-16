<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/models/GameModel.php';
require_once __DIR__ . '/../../app/models/AuditModel.php';

$id = (int)($_GET['id'] ?? 0);
$game = GameModel::find($id);
if (!$game) { http_response_code(404); exit('No encontrado'); }

/* edición abierta a socios */

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf_token();
    $name = trim($_POST['name'] ?? '');
    if ($name === '') { $err = 'El nombre es obligatorio.'; }
    else {
        try {
            GameModel::update($id, $name, current_user()['id']);
            AuditModel::log('games', 'update', $id, ['name'=>$name], current_user()['id']);
            header('Location: /games/list.php'); exit;
        } catch (Throwable $e) { $err = 'No se pudo actualizar. ¿Existe ya un juego con este nombre?'; }
    }
}
require_once __DIR__ . '/../includes/header.php';
require_login();
?>
<div class="col-md-6">
  <h4>Editar juego</h4>
  <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input class="form-control" name="name" value="<?= htmlspecialchars($game['name']) ?>" required>
    </div>
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="/games/list.php">Volver</a>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
