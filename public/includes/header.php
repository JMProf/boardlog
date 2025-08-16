<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../../app/models/SettingsModel.php';
start_secure_session();
$csrf = generate_csrf_token();
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars(SettingsModel::get('app_name', APP_NAME)) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-body">
    <nav class="navbar navbar-expand-lg bg-body-tertiary mb-4">
      <div class="container">
        <a class="navbar-brand" href="/"><?= htmlspecialchars(SettingsModel::get('app_name', APP_NAME)) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="/">Gráfica</a></li>
            <?php if (current_user()): ?><li class="nav-item"><a class="nav-link" href="/games/list.php">Juegos</a></li>
              <li class="nav-item"><a class="nav-link" href="/plays/list.php">Partidas</a></li>
              <?php if (is_superadmin()): ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Admin</a>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/admin/users.php">Usuarios</a></li>
                    <li><a class="dropdown-item" href="/admin/audit.php">Auditoría</a></li>
                    <li><a class="dropdown-item" href="/admin/settings.php">Ajustes</a></li></ul>
                </li>
              <?php endif; ?>
            <?php endif; ?></ul>
          <ul class="navbar-nav">
            
            <?php if (current_user()): ?><li class="nav-item"><a class="nav-link" href="/account/password.php">Mi cuenta</a></li>
              <li class="nav-item"><a class="btn btn-outline-secondary" href="/logout.php">Salir</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="btn btn-primary" href="/login.php">Entrar</a></li>
            <?php endif; ?>
            <li class="nav-item ms-2"><button id="themeToggle" class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleTheme()">🌙</button></li>
</ul>
      </div>
    </nav>
    <div class="container">
