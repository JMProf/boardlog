<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/schema.php';

/**
 * Bootstrap del superadmin SOLO en el primer arranque.
 * - Usa ADMIN_USERNAME/ADMIN_PASSWORD si están definidos; si no, 'admin'/'admin'.
 * - Si el usuario existe lo actualiza (password + rol superadmin); si no existe lo crea.
 * - Marca en settings.admin_bootstrap_sig para no repetir.
 */
function bootstrap_admin_from_env_once(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $adminUser = getenv('ADMIN_USERNAME') ?: 'admin';
    $adminPass = getenv('ADMIN_PASSWORD') ?: '';

    try {
        $pdo = get_pdo();
        // Asegurar tabla settings
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (skey VARCHAR(100) PRIMARY KEY, svalue TEXT NOT NULL) ENGINE=InnoDB");

        // ¿Ya se hizo bootstrap?
        $stmt = $pdo->prepare("SELECT svalue FROM settings WHERE skey = 'admin_bootstrap_sig'");
        $stmt->execute();
        $already = $stmt->fetchColumn();
        if ($already !== false) {
            return; // ya marcado: no tocar nada
        }

        // Determinar contraseña de primer arranque
        $firstPassword = ($adminPass !== '') ? $adminPass : 'admin';
        $hash = password_hash($firstPassword, PASSWORD_DEFAULT);

        // Buscar usuario
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
        $stmt->execute([':u' => $adminUser]);
        $id = $stmt->fetchColumn();

        if ($id) {
            $stmt = $pdo->prepare("UPDATE users SET password_hash = :p, role = 'superadmin' WHERE id = :id");
            $stmt->execute([':p' => $hash, ':id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :p, 'superadmin')");
            $stmt->execute([':u' => $adminUser, ':p' => $hash]);
        }

        // Marcar bootstrap hecho
        $sig = hash('sha256', $adminUser . '|' . $firstPassword);
        $stmt = $pdo->prepare("INSERT INTO settings (skey, svalue) VALUES ('admin_bootstrap_sig', :v)");
        $stmt->execute([':v' => $sig]);
    } catch (Throwable $e) {
        // Silencio: reintenta en la próxima petición si la BD aún no está lista
    }
}

/**
 * Inicia sesión segura y garantiza que el esquema de BD existe.
 */
function start_secure_session(): void
{
    // Asegurar que el esquema existe antes de cualquier uso de BD
    ensure_schema();

    // Ejecutar bootstrap de admin solo en el primer arranque
    bootstrap_admin_from_env_once();

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => COOKIE_SECURE,
            'httponly' => COOKIE_HTTPONLY,
            'samesite' => COOKIE_SAMESITE,
        ]);
        session_name(SESSION_NAME);
        session_start();
    }
    if (empty($_SESSION['initiated'])) {
        $_SESSION['initiated'] = true;
        session_regenerate_id(true);
    }
}

/**
 * CSRF helpers
 */
function generate_csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION[CSRF_TOKEN_KEY])) {
        $_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_KEY];
}

function check_csrf_token(): void
{
    start_secure_session();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST[CSRF_TOKEN_KEY] ?? '';
        if (!$token || !hash_equals($_SESSION[CSRF_TOKEN_KEY] ?? '', $token)) {
            http_response_code(400);
            exit('Token CSRF inválido.');
        }
    }
}

/**
 * Autenticación y autorización
 */
function current_user(): ?array
{
    start_secure_session();
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /login.php');
        exit;
    }
}

function is_superadmin(): bool
{
    $u = current_user();
    return $u && ($u['role'] ?? '') === 'superadmin';
}

function require_superadmin(): void
{
    if (!is_superadmin()) {
        http_response_code(403);
        exit('Acceso denegado.');
    }
}

function login(string $username, string $password): bool
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        start_secure_session();
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        session_regenerate_id(true);
        return true;
    }
    return false;
}

function logout(): void
{
    start_secure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
    }
    session_destroy();
}
