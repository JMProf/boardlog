<?php
// public/login.php
require_once __DIR__ . '/../app/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf_token();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (login($u, $p)) {
        header('Location: /');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-3">Entrar</h5>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= htmlspecialchars($csrf) ?>">
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input class="form-control" type="password" name="password" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Entrar</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
