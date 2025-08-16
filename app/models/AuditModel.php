<?php
declare(strict_types=1);
require_once __DIR__ . '/../../app/db.php';

class AuditModel {
    public static function log(string $table, string $action, int $record_id, array $changes, int $actor_id): void {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("INSERT INTO audit_logs (table_name, action, record_id, changes_json, actor_user_id) VALUES (:t, :a, :r, :c, :u)");
        $stmt->execute([':t'=>$table, ':a'=>$action, ':r'=>$record_id, ':c'=>json_encode($changes, JSON_UNESCAPED_UNICODE), ':u'=>$actor_id]);
    }
    public static function list(int $limit = 200): array {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("SELECT al.*, u.username FROM audit_logs al LEFT JOIN users u ON u.id = al.actor_user_id ORDER BY al.created_at DESC LIMIT :lim");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public static function purgeAll(): void {
        $pdo = get_pdo();
        $pdo->exec("TRUNCATE TABLE audit_logs");
    }
}
