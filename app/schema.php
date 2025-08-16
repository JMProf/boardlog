<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

/**
 * Crea/esquema la base de datos si no existe (idempotente).
 * Guarda en settings.schema_version para referencia futura.
 */
function ensure_schema(): void {
    try {
        $pdo = get_pdo();
        // Charset/collation por defecto al nivel de tabla.
        $stmts = [
            // settings primero (lo usa bootstrap)
            "CREATE TABLE IF NOT EXISTS settings (
                skey VARCHAR(100) PRIMARY KEY,
                svalue TEXT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci",

            // users
            "CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('socio','superadmin') NOT NULL DEFAULT 'socio',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci",

            // games
            "CREATE TABLE IF NOT EXISTS games (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                name_norm VARCHAR(255) NOT NULL,
                created_by INT UNSIGNED NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_by INT UNSIGNED NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_games_name_norm (name_norm),
                INDEX idx_games_created_at (created_at),
                CONSTRAINT fk_games_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT fk_games_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci",

            // plays
            "CREATE TABLE IF NOT EXISTS plays (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                game_id INT UNSIGNED NOT NULL,
                players_count INT UNSIGNED NOT NULL,
                played_at DATE NOT NULL,
                created_by INT UNSIGNED NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_by INT UNSIGNED NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                INDEX idx_plays_game_date (game_id, played_at),
                CONSTRAINT fk_plays_game FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
                CONSTRAINT fk_plays_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT fk_plays_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci",

            // audit logs
            "CREATE TABLE IF NOT EXISTS audit_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                table_name VARCHAR(64) NOT NULL,
                action ENUM('create','update','delete','purge','settings') NOT NULL,
                record_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
                changes_json JSON NULL,
                actor_user_id INT UNSIGNED NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_audit_created (created_at),
                CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci"
        ];

        foreach ($stmts as $sql) {
            $pdo->exec($sql);
        }

        // schema_version simple
        $stmt = $pdo->prepare("SELECT svalue FROM settings WHERE skey='schema_version'");
        $stmt->execute();
        $ver = $stmt->fetchColumn();
        if ($ver === false) {
            $stmt = $pdo->prepare("INSERT INTO settings (skey, svalue) VALUES ('schema_version', '1')");
            $stmt->execute();
        }
    } catch (Throwable $e) {
        // Silencioso para no romper cabeceras; se reintenta en la siguiente petición.
    }
}
