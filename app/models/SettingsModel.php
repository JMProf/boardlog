<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/db.php';

class SettingsModel {
    public static function get(string $key, ?string $default = null): ?string {
        $pdo = get_pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (skey VARCHAR(100) PRIMARY KEY, svalue TEXT NOT NULL) ENGINE=InnoDB");
        $stmt = $pdo->prepare("SELECT svalue FROM settings WHERE skey = :k");
        $stmt->execute([':k' => $key]);
        $val = $stmt->fetchColumn();
        if ($val === false) return $default;
        return (string)$val;
    }
    public static function set(string $key, string $value): void {
        $pdo = get_pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (skey VARCHAR(100) PRIMARY KEY, svalue TEXT NOT NULL) ENGINE=InnoDB");
        $stmt = $pdo->prepare("INSERT INTO settings (skey, svalue) VALUES (:k, :v) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)");
        $stmt->execute([':k' => $key, ':v' => $value]);
    }
}
